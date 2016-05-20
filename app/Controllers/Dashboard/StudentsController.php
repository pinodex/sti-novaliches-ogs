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
use App\Models\Student;
use App\Services\View;
use App\Services\Form;
use App\Services\Helper;
use App\Services\Settings;
use App\Services\FlashBag;
use App\Controllers\Controller;
use App\Constraints as CustomAssert;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Route controller for administrator students pages
 */
class StudentsController extends Controller
{
    /**
     * Student page index
     * 
     * URL: /dashboard/students/
     */
    public function index(Request $request)
    {
        if ($page = $request->query->get('page')) {
            Paginator::currentPageResolver(function () use ($page) {
                return $page;
            });
        }

        $context = array();

        $form = Form::create($request->query->all(), array(
            'csrf_protection' => false
        ));
        
        $form->add('id', 'text', array(
            'label'     => 'Search by number',
            'required'  => false
        ));
        
        $form->add('name', 'text', array(
            'label'     => 'Search by name',
            'required'  => false
        ));

        $context['search_form'] = $form->getForm()->createView();
        
        $query = array();
        $builderHook = null;

        $request->query->set('id', Helper::parseId($request->query->get('id')));

        if ($id = $request->query->get('id')) {
            $query[] = array('id', 'LIKE', $id);
        }

        if ($name = $request->query->get('name')) {
            $query[] = array('name', 'LIKE', '%' . $name . '%');
        }

        if ($this->isRole('faculty')) {
            $builderHook = function (Builder $builder) {
                $builder->leftJoin('grades', 'students.id', '=', 'grades.student_id');
                $builder->where('importer_id', $this->user->getModel()->id);
            };
        }

        $context['result'] = Student::search($query, null, $builderHook)->toArray();

        return View::render('dashboard/students/index', $context);
    }

    /**
     * View student page
     * 
     * URL: /dashboard/students/{id}
     */
    public function view(Application $app, $id)
    {
        if (!$student = Student::with('grades')->find($id)) {
            $app->abort(404);
        }

        if ($this->isRole('faculty')) {
            $gradesFromImporter = Grade::where(array(
                'student_id' => $student->id,
                'importer_id' => $this->user->getModel()->id
            ));


            if ($gradesFromImporter->count() == 0) {
                $app->abort(403);
            }
        }

        $context = array(
            'student'       => $student->toArray(),
            'grades'        => $student->grades->toArray(),
            'period'        => strtolower(Settings::get('period', 'prelim'))
        );

        $context['active_period'] = array_flip(array('prelim', 'midterm', 'prefinal', 'final'))[$context['period']];

        return View::render('dashboard/students/view', $context);
    }

    /**
     * Add/Edit student info
     * 
     * URL: /dashboard/students/add
     * URL: /dashboard/students/{id}/edit
     */
    public function edit(Request $request, Application $app, $id)
    {
        $mode = 'add';
        $student = Student::findOrNew($id);

        if ($student->id != $id) {
            $app->abort(404);
        }

        $id && $mode = 'edit';
        $form = Form::create($student->toArray());

        $form->add('id', 'text', array(
            'label' => 'Student ID',

            'constraints' => array(
                new Assert\Regex(array(
                    'pattern'   => '/([\d+]{3}-[\d+]{4}-[\d+]{4})|([\d+]{3})([\d+]{4})([\d+]{4})/',
                    'match'     => true,
                    'message'   => 'Invalid Student ID format'
                )),

                new CustomAssert\UniqueRecord(array(
                    'model'     => 'App\Models\Student',
                    'exclude'   => $student->id,
                    'row'       => 'id',
                    'message'   => 'Student ID already in use.'
                ))
            )
        ));

        $form->add('first_name', 'text');
        $form->add('middle_name', 'text');
        $form->add('last_name', 'text');
        $form->add('course', 'text');

        $form->add('mobile_number', 'text', array(
            'constraints' => new Assert\Regex(array(
                'pattern'   => '/(0|63|\+63)[\d+]{10}/',
                'message'   => 'Please enter a valid mobile number',
                'match'     => true
            )),

            'required' => false
        ));

        $form->add('landline', 'text', array(
            'constraints' => new Assert\Regex(array(
                'pattern'   => '/([\d+]{3}[\d+]{4})|([\d+]{3}-[\d+]{4})/',
                'message'   => 'Please enter a valid landline number',
                'match'     => true
            )),

            'required' => false
        ));

        $form->add('email_address', 'text', array(
            'constraints' => new Assert\Email(),
            'required' => false
        ));

        $form->add('address', 'textarea', array(
            'label' => 'Address',
            'required' => false
        ));

        $form->add('guardian_name', 'text', array(
            'label' => 'Name of guardian/parent',
            'required' => false
        ));

        $form->add('guardian_contact_number', 'text', array(
            'label' => 'Guardian\'s/Parent\'s contact no.',
            'constraints' => new Assert\Regex(array(
                'pattern'   => '/([\d+]{3}[\d+]{4})|([\d+]{3}-[\d+]{4})|((0|63|\+63)[\d+]{10})/',
                'message'   => 'Please enter a valid mobile number or landline',
                'match'     => true
            )),

            'required' => false
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $student->fill($form->getData());
            $student->save();

            FlashBag::add('messages', 'success>>>Student has been saved');

            return $app->redirect($app->path('dashboard.students.view', array(
                'id' => $student->id
            )));
        }

        return View::render('dashboard/students/' . $mode, array(
            'form'      => $form->createView(),
            'student'   => $student->toArray()
        ));
    }

    /**
     * Edit student page
     * 
     * URL: /dashboard/students/{id}/edit/grades
     */
    public function editGrades(Request $request, Application $app, $id)
    {
        $student = Student::with('grades')->findOrFail($id);
        $grades = $student->grades->toArray();

        if (empty($grades)) {
            FlashBag::add('messages', 'danger>>>Nothing has been imported and can be edited for this student yet.');

            return $app->redirect($app->path('dashboard.students.view', array(
                'id' => $id
            )));
        }

        $subjectSet = array();

        foreach ($grades as $grade) {
            $subjectSet[] = $grade['subject'];
        }

        if ($request->getMethod() == 'POST') {
            $gradesInput = $request->request->get('grades');

            // Check if the input subjects matches the current subject set
            foreach ($gradesInput as $i => $inputItem) {
                if (!in_array($inputItem['subject'], $subjectSet)) {
                    unset($gradesInput[$i]);
                }
            }

            $student->updateGrades($gradesInput);

            return $app->redirect($app->path('dashboard.students.view', array(
                'id' => $id
            )));
        }

        return View::render('dashboard/students/edit.grades', array(
            'student'   => $student,
            'grades'    => $grades
        ));
    }

    /**
     * Delete student
     * 
     * URL: /dashboard/students/{id}/delete
     */
    public function delete(Request $request, Application $app, $id)
    {
        if (!$student = Student::find($id)) {
            $app->abort(404);
        }

        $form = Form::create();
        $form->add('_confirm', 'hidden');

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid() || $this->isTokenValid('students.delete', $request)) {
            $student->delete();

            FlashBag::add('messages', 'info>>>Student has been deleted');

            return $app->redirect($app->path('dashboard.students'));
        }

        return View::render('dashboard/students/delete', array(
            'form'      => $form->createView(),
            'student'   => $student->toArray()
        ));
    }
}
