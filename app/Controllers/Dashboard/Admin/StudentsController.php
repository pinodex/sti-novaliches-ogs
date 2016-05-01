<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers\Dashboard\Admin;

use Silex\Application;
use App\Models\Student;
use App\Services\View;
use App\Services\Form;
use App\Services\Helper;
use App\Services\OmegaSheet;
use App\Services\Session;
use App\Services\Session\FlashBag;
use App\Constraints as CustomAssert;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;

/**
 * Route controller for administrator students pages
 */
class StudentsController
{
    /**
     * Faculty page index
     * 
     * URL: /dashboard/students/
     */
    public function index(Request $request)
    {
        if ($page = $request->query->get('page')) {
            Paginator::currentPageResolver(function() use($page) {
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

        $request->query->set('id', Helper::parseId(
            $request->query->get('id')
        ));

        $result = Student::search(
            $request->query->get('id'),
            $request->query->get('name')
        );

        return View::render('dashboard/students/index', array(
            'search_form'   => $form->createView(),
            'current_page'  => $result->currentPage(),
            'last_page'     => $result->lastPage(),
            'result'        => $result
        ));
    }

    /**
     * Faculty student view
     * 
     * URL: /dashboard/students/{id}
     */
    public function view(Application $app, $id)
    {
        $student = Student::with('grades')->find($id);

        if (!$student) {
            FlashBag::add('messages', 'danger>>>Student not found');

            return $app->redirect($app->path('dashboard.students'));
        }

        return View::render('dashboard/students/view', array(
            'student'   => $student->toArray(),
            'grades'    => $student->grades->toArray()
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
     * Faculty student edit
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

        if ($form->isValid()) {
            $student->delete();

            FlashBag::add('messages', 'info>>>Student has been deleted');

            return $app->redirect($app->path('dashboard.students'));
        }

        return View::render('dashboard/students/delete', array(
            'form'      => $form->createView(),
            'student'   => $student->toArray()
        ));
    }

    /**
     * Student import wizard index
     * 
     * URL: /dashboard/students/import
     */
    public function import(Application $app) {
        return $app->redirect($app->path('dashboard.students.import.1'));
    }

    /**
     * Student import wizard step 1
     * 
     * URL: /dashboard/students/import/1
     */
    public function import1(Request $request, Application $app) {
        if ($uploadedFile = Session::get('sw_uploaded_file')) {
            @unlink($uploadedFile);
        }

        // cleanup first
        Session::remove('sw_uploaded_file');
        Session::remove('sw_selected_sheets');
        Session::remove('sw_import_done');
        Session::remove('sw_contents');

        $form = Form::create();
        $fs = new Filesystem();

        $form->add('file', 'file', array(
            'label' => ' ',
            'attr'  => array(
                'accept' => 'text/csv'
            )
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $file = $form['file']->getData();

            if ($file->getError() != 0) {
                FlashBag::add('messages', 'danger>>>' . $file->getErrorMessage());
                
                return $app->redirect($app->path('dashboard.students.import.1'));
            }

            $extension = $form['file']->getData()->guessExtension();
            
            $name = ROOT . 'storage/' . sprintf('%s-%s.%s',
                date('Y-m-d-H-i-s'), uniqid(), $extension
            );

            /**
             * We need to convert it to utf-8 to store special and accented characters
             */
            $contents = file_get_contents($form['file']->getData()->getPathName());
            $fs->dumpFile($name, mb_convert_encoding($contents, 'UTF-8', 'pass'));

            Session::set('sw_uploaded_file', $name);
            
            return $app->redirect($app->path('dashboard.students.import.2'));
        }

        return View::render('dashboard/students/import/1', array(
            'upload_form'   => $form->createView(),
            'current_step'  => 1
        ));
    }

    /**
     * Student import wizard step 2
     * 
     * URL: /dashboard/students/import/2
     */
    public function import2(Request $request, Application $app) {
        if (!$uploadedFile = Session::get('sw_uploaded_file')) {
            return $app->redirect($app->path('dashboard.students.import.1'));
        }

        $omegaSheet = new OmegaSheet($uploadedFile);
        $sheets = array();

        foreach ($omegaSheet->getSheets() as $index => $sheet) {
            $sheets[$sheet] = $index;
        }

        $form = Form::create();

        $form->add('choices', 'choice', array(
            'choices'           => $sheets,
            'choices_as_values' => true,
            'label'             => ' ',
            'multiple'          => true,
            'expanded'          => true
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form['choices']->getData();

            if (count($data) > 3) {
                FlashBag::add('messages', 'danger>>>You can only select/import up to 3 sheets at a time.');
                return $app->redirect($app->path('dashboard.students.import.2'));
            }

            if ($previousData = Session::get('sw_selected_sheets')) {
                // Check if there are changes to sheet selection before busting the cache
                if ($previousData != $data) {
                    Session::remove('sw_contents');
                }
            }

            Session::set('sw_selected_sheets', $data);
            return $app->redirect($app->path('dashboard.students.import.3'));
        }

        return View::render('dashboard/students/import/2', array(
            'choose_form'   => $form->createView(),
            'current_step'  => 2
        ));
    }

    /**
     * Student import wizard step 3
     * 
     * URL: /dashboard/students/import/3
     */
    public function import3(Request $request, Application $app) {
        if (!$uploadedFile = Session::get('sw_uploaded_file')) {
            return $app->redirect($app->path('dashboard.students.import.1'));
        }

        if (!$selectedSheets = Session::get('sw_selected_sheets')) {
            return $app->redirect($app->path('dashboard.students.import.2'));
        }

        /* Check if spreadsheet contents is cached in the session database
           Used remove the need to load the spreadsheet file again, thus saving time */
        if (!$contents = Session::get('sw_contents')) {
            set_time_limit(0);
            
            $omegaSheet = new OmegaSheet($uploadedFile);
            $contents = $omegaSheet->getSheetsContents($selectedSheets);

            Session::set('gw_contents', $contents);
        }

        $rowCount = 0;

        foreach ($contents as $content) {
            $rowCount += count($content);
        }

        $form = Form::create();

        $form->add('_confirm', 'hidden', array(
            'required' => false
        ));

        $form->add('purge', 'checkbox', array(
            'label'     => 'Purge student master list before import',
            'required'  => false
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form['purge']->getData()) {
                Student::truncate();
            }

            Student::import($contents);
            Session::set('sw_import_done', true);

            return $app->redirect($app->path('dashboard.students.import.4'));
        }

        return View::render('dashboard/students/import/3', array(
            'current_step'  => 3,
            'confirm_form'  => $form->createView(),
            'row_count'     => $rowCount
        ));
    }

    /**
     * Student import wizard step 4
     * 
     * URL: /dashboard/students/import/4
     */
    public function import4(Application $app) {
        if (!$uploadedFile = Session::get('sw_uploaded_file')) {
            return $app->redirect($app->path('dashboard.students.import.1'));
        }

        if (!Session::get('sw_selected_sheets')) {
            return $app->redirect($app->path('dashboard.students.import.2'));
        }

        if (!Session::get('sw_import_done')) {
            return $app->redirect($app->path('dashboard.students.import.3'));
        }

        // cleanup
        Session::remove('sw_uploaded_file');
        Session::remove('sw_selected_sheets');
        Session::remove('sw_import_done');
        Session::remove('sw_contents');

        @unlink($uploadedFile);

        return View::render('dashboard/students/import/4', array(
            'current_step' => 4
        ));
    }
}
