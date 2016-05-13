<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers\Dashboard;

use Silex\Application;
use App\Models\Grade;
use App\Models\Faculty;
use App\Models\Department;
use App\Services\Auth;
use App\Services\View;
use App\Services\Form;
use App\Services\Session;
use App\Services\Settings;
use App\Services\FlashBag;
use App\Controllers\Controller;
use App\Constraints as CustomAssert;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Route controller for faculty management pages
 */
class FacultyController extends Controller
{
    /**
     * Manage faculty accounts page
     * 
     * URL: /dashboard/faculty/
     */
    public function index(Request $request)
    {
        if ($page = $request->query->get('page')) {
            Paginator::currentPageResolver(function () use ($page) {
                return $page;
            });
        }

        $form = Form::create(null, array(
            'csrf_protection' => false
        ));
        
        $form->add('name', 'text', array(
            'label'     => 'Name',
            'required'  => false,
            'data'      => $request->query->get('name')
        ));

        $form = $form->getForm();
        $result = Faculty::search(null, $request->query->get('name'));

        return View::render('dashboard/faculty/index', array(
            'search_form'   => $form->createView(),
            'result'        => $result->toArray()
        ));
    }

    /**
     * Manage faculty summary accounts page
     * 
     * URL: /dashboard/faculty/summary
     */
    public function summary()
    {
        $faculty = Faculty::all();
        $period = strtolower(Settings::get('period', 'prelim'));
        $periodIndex = array_flip(array('prelim', 'midterm', 'prefinal', 'final'))[$period];

        return View::render('dashboard/faculty/summary', array(
            'faculty'       => $faculty,
            'period'        => $period,
            'active_period' => $periodIndex
        ));
    }

    /**
     * View faculty account page
     * 
     * URL: /dashboard/faculty/{id}
     */
    public function view(Application $app, $id)
    {
        if (!$faculty = Faculty::with('department', 'submissionLogs')->find($id)) {
            FlashBag::add('messages', 'danger>>>Faculty account not found');
            return $app->redirect($app->path('dashboard.faculty'));
        }

        if ($this->isRole('head')) {
            // Deny if the faculty and head does not belong to the same department
            if (!$faculty->department || $faculty->department->id != $user->getModel()->department->id) {
                FlashBag::add('messages', 'danger>>>This faculty is not in your department');

                return $app->redirect($app->path('dashboard.departments.view', array(
                    'id' => $model->department->id
                )));
            }
        }

        $sections = array();
        $period = strtolower(Settings::get('period', 'prelim'));
        $periodIndex = array_flip(array('prelim', 'midterm', 'prefinal', 'final'))[$period];

        $gradeGroups = $faculty->submittedGrades->groupBy(function (Grade $grade) {
            return $grade->subject . ' ' . $grade->section;
        });

        foreach ($gradeGroups as $id => $grades) {
            $withoutGradesCount = array(
                'prelim'    => 0,
                'midterm'   => 0,
                'prefinal'  => 0,
                'final'     => 0
            );

            foreach ($grades as $grade) {
                if ($grade->getOriginal('prelim_grade') === null) {
                    $withoutGradesCount['prelim']++;
                }

                if ($grade->getOriginal('midterm_grade') === null) {
                    $withoutGradesCount['midterm']++;
                }

                if ($grade->getOriginal('prefinal_grade') === null) {
                    $withoutGradesCount['prefinal']++;
                }

                if ($grade->getOriginal('final_grade') === null) {
                    $withoutGradesCount['final']++;
                }
            }

            $sections[] = array(
                'id'                            => $id,
                'student_count'                 => count($grades),
                'student_without_grades_count'  => $withoutGradesCount
            );
        }

        return View::render('dashboard/faculty/view', array(
            'faculty'       => $faculty->toArray(),
            'sections'      => $sections,
            'active_period' => $periodIndex,
            'period'        => $period,
            'logs'          => array_reverse($faculty->submissionLogs->toArray()),

            'statuses' => array(
                $faculty->getStatusAttribute('prelim'),
                $faculty->getStatusAttribute('midterm'),
                $faculty->getStatusAttribute('prefinal'),
                $faculty->getStatusAttribute('final')
            ),

            'stats' => array(
                'failed' => array(
                    'prelim'    => $faculty->getNumberOfFailsAttribute('prelim'),
                    'midterm'   => $faculty->getNumberOfFailsAttribute('midterm'),
                    'prefinal'  => $faculty->getNumberOfFailsAttribute('prefinal'),
                    'final'     => $faculty->getNumberOfFailsAttribute('final')
                ),

                'dropped' => array(
                    'prelim'    => $faculty->getNumberOfDropsAttribute('prelim'),
                    'midterm'   => $faculty->getNumberOfDropsAttribute('midterm'),
                    'prefinal'  => $faculty->getNumberOfDropsAttribute('prefinal'),
                    'final'     => $faculty->getNumberOfDropsAttribute('final'),
                )
            )
        ));
    }

    /**
     * Edit faculty account page
     * 
     * URL: /dashboard/faculty/add
     * URL: /dashboard/faculty/{id}/edit
     */
    public function edit(Request $request, Application $app, $id)
    {
        $mode = 'add';
        $faculty = Faculty::findOrNew($id);

        if ($faculty->id != $id) {
            FlashBag::add('messages', 'danger>>>Faculty account not found');
            return $app->redirect($app->path('dashboard.faculty'));
        }

        $id && $mode = 'edit';
        $form = Form::create($faculty->toArray());

        $departments = Department::getFormChoices();
        $departments['0'] = 'Unassigned';

        $form->add('first_name', 'text');
        $form->add('middle_name', 'text');
        $form->add('last_name', 'text');

        $form->add('department_id', 'choice', array(
            'label'         => 'Department',
            'choices'       => $departments,
            'data'          => $faculty->department_id ?: '0'
        ));

        $form->add('username', 'text', array(
            'constraints' => new CustomAssert\UniqueRecord(array(
                'model'     => 'App\Models\Faculty',
                'exclude'   => $faculty->username,
                'row'       => 'username',
                'message'   => 'Username already in use.'
            ))
        ));

        $form->add('password', 'repeated', array(
            'type'      => 'password',
            'required'  => false,

            'first_options' => array(
                'label' => 'Password (leave blank if not changing)'
            ),

            'second_options' => array(
                'label' => 'Repeat Password (leave blank if not changing)'
            ),

            'constraints' => new Assert\Length(array(
                'min'        => 8,
                'minMessage' => 'Password should be at least 8 characters long'
            ))
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if ($data['password'] === null) {
                unset($data['password']);
            }

            $faculty->fill($data);
            $faculty->save();

            FlashBag::add('messages', 'success>>>Faculty account has been saved');

            return $app->redirect($app->path('dashboard.faculty'));
        }

        return View::render('dashboard/faculty/' . $mode, array(
            'form'      => $form->createView(),
            'faculty'   => $faculty->toArray()
        ));
    }

    /**
     * Delete faculty account page
     * 
     * URL: /dashboard/faculty/{id}/delete
     */
    public function delete(Request $request, Application $app, $id)
    {
        if (!$faculty = Faculty::find($id)) {
            FlashBag::add('messages', 'danger>>>Faculty account not found');

            return $app->redirect($app->path('dashboard.faculty'));
        }

        $form = Form::create();
        $form->add('_confirm', 'hidden');

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $faculty->delete();

            FlashBag::add('messages', 'info>>>Faculty account has been deleted');

            return $app->redirect($app->path('dashboard.faculty'));
        }

        return View::render('dashboard/faculty/delete', array(
            'form'      => $form->createView(),
            'faculty'   => $faculty->toArray()
        ));
    }
}
