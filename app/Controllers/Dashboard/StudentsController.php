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

        $form = Form::create(null, array(
            'csrf_protection' => false
        ));
        
        $form->add('id', 'text', array(
            'label'     => 'Search by number',
            'required'  => false,
            'data'      => $request->query->get('id')
        ));
        
        $form->add('name', 'text', array(
            'label'     => 'Search by name',
            'required'  => false,
            'data'      => $request->query->get('name')
        ));

        $result = array();
        $form = $form->getForm();

        $request->query->set('id', Helper::parseId($request->query->get('id')));

        if ($this->isRole('faculty')) {
            $result = Student::filteredSearch(
                $request->query->get('id'),
                $request->query->get('name'),
                $this->user->getModel()->id
            );
        } else {
            $result = Student::search($request->query->get('id'), $request->query->get('name'));
        }

        return View::render('dashboard/students/index', array(
            'search_form'   => $form->createView(),
            'result'        => $result->toArray()
        ));
    }

    /**
     * View student page
     * 
     * URL: /dashboard/students/{id}
     */
    public function view(Application $app, $id)
    {
        if (!$student = Student::with('grades')->find($id)) {
            FlashBag::add('messages', 'danger>>>Student not found');
            return $app->redirect($app->path('dashboard.students'));
        }

        if ($this->isRole('faculty')) {
            $gradesFromImporter = Grade::where(array(
                'student_id' => $student->id,
                'importer_id' => $this->user->getModel()->id
            ));


            if ($gradesFromImporter->count() == 0) {
                FlashBag::add('messages', 'danger>>>You are not allowed to perform this action');
                return $app->redirect($app->path('dashboard.students'));
            }
        }


        $period = strtolower(Settings::get('period', 'prelim'));
        $periodIndex = array_flip(array('prelim', 'midterm', 'prefinal', 'final'))[$period];

        return View::render('dashboard/students/view', array(
            'student'       => $student->toArray(),
            'grades'        => $student->grades->toArray(),
            'period'        => $period,
            'active_period' => $periodIndex
        ));
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
            FlashBag::add('messages', 'danger>>>Student not found');
            return $app->redirect($app->path('dashboard.students'));
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
            'label' => 'Mobile number *',
            'constraints' => new Assert\Regex(array(
                'pattern'   => '/(0|63|\+63)[\d+]{10}/',
                'message'   => 'Please enter a valid mobile number',
                'match'     => true
            ))
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
            'label'       => 'Email address *',
            'constraints' => new Assert\Email()
        ));

        $form->add('address', 'textarea', array(
            'label' => 'Address *'
        ));

        $form->add('guardian_name', 'text', array(
            'label' => 'Name of guardian/parent *'
        ));

        $form->add('guardian_contact_number', 'text', array(
            'label' => 'Guardian\'s/Parent\'s contact no. *',
            'constraints' => new Assert\Regex(array(
                'pattern'   => '/([\d+]{3}[\d+]{4})|([\d+]{3}-[\d+]{4})|((0|63|\+63)[\d+]{10})/',
                'message'   => 'Please enter a valid mobile number or landline',
                'match'     => true
            ))
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
            FlashBag::add('messages', 'danger>>>Student not found');

            return $app->redirect($app->path('dashboard.students'));
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
