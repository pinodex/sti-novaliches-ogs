<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers;

use Silex\Application;
use App\Models\Grade;
use App\Models\Student;
use App\Services\Form;
use App\Services\View;
use App\Services\Helper;
use App\Services\GradingSheet;
use App\Services\Session\Session;
use App\Services\Session\FlashBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use Illuminate\Pagination\Paginator;

/**
 * Faculty controller
 * 
 * Route controllers for faculty pages (/faculty/*)
 */
class FacultyController
{
    /**
     * Faculty page index
     * 
     * URL: /faculty/
     */
    public function index(Request $request, Application $app)
    {
        if ($page = $request->query->get('page')) {
            Paginator::currentPageResolver(function() use($page) {
                return $page;
            });
        }

        $form = Form::create(null, array(
            'csrf_protection' => false
        ));
        
        $form->add('id', Type\TextType::class, array(
            'label' => 'Search by number',
            'required' => false,
            'data' => $request->query->get('id')
        ));
        
        $form->add('name', Type\TextType::class, array(
            'label' => 'Search by name',
            'required' => false,
            'data' => $request->query->get('name')
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

        return View::render('faculty/index', array(
            'search_form' => $form->createView(),
            'current_page' => $result->currentPage(),
            'last_page' => $result->lastPage(),
            'result' => $result
        ));
    }

    /**
     * Faculty student view
     * 
     * URL: /faculty/student/{id}
     */
    public function studentsView(Application $app, $id)
    {
        $student = Student::with('grades')->find($id);

        if (!$student) {
            FlashBag::add('messages', 'danger>>>Student not found');

            return $app->redirect($app->path('faculty.index'));
        }

        return View::render('faculty/students/view', array(
            'student' => $student->toArray(),
            'grades' => $student->grades->toArray()
        ));
    }

    /**
     * Faculty student edit
     * 
     * URL: /faculty/student/{id}/edit
     */
    public function studentsEdit(Request $request, Application $app, $id)
    {
        $student = Student::with('grades')->findOrFail($id);
        $grades = $student->grades->toArray();

        if (empty($grades)) {
            FlashBag::add('messages', 'danger>>>Nothing has been imported and can be edited for this student yet.');

            return $app->redirect($app->path('faculty.students.view', array(
                'id' => $id
            )));
        }

        if ($request->getMethod() == 'POST') {
            $subjects = $request->request->get('subjects');
            $student->updateGrades($subjects);

            return $app->redirect($app->path('faculty.students.view', array(
                'id' => $id
            )));
        }

        return View::render('faculty/students/edit', array(
            'student' => $student,
            'grades' => $grades
        ));
    }

    /**
     * Grade import wizard index
     * 
     * URL: /faculty/grades/import
     */
    public function gradesImport(Application $app) {
        return $app->redirect($app->path('faculty.grades.import.1'));
    }

    /**
     * Grade import wizard step 1
     * 
     * URL: /faculty/grades/import/1
     */
    public function gradesImport1(Request $request, Application $app) {
        if ($uploadedFile = Session::get('gw_uploaded_file')) {
            @unlink($uploadedFile);
        }

        // cleanup first
        Session::remove('gw_uploaded_file');
        Session::remove('gw_selected_sheets');
        Session::remove('gw_import_done');
        Session::remove('gw_contents');

        $form = Form::create();

        $form->add('file', Type\FileType::class, array(
            'label' => ' ',
            'constraints' => new Assert\File(array(
                'mimeTypesMessage' => 'Please upload a valid XLSX/XLSM file',
                'mimeTypes' => array(
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel.sheet.macroEnabled.12'
                )
            ))
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $file = $form['file']->getData();

            if ($file->getError() != 0) {
                FlashBag::add('messages', 'danger>>>' . $file->getErrorMessage());
                
                return $app->redirect($app->path('faculty.grades.import.1'));
            }

            $mime = $form['file']->getData()->getMimeType();
            $extension = $form['file']->getData()->guessExtension();
            
            $uploadedFile = $form['file']->getData()->move(ROOT . 'storage', sprintf('%s-%s.%s',
                date('Y-m-d-H-i-s'), uniqid(), $extension
            ));

            Session::set('gw_uploaded_file', $uploadedFile->getPathName());
            
            return $app->redirect($app->path('faculty.grades.import.2'));
        }

        return VieW::render('faculty/grades/import/1', array(
            'upload_form' => $form->createView(),
            'current_step' => 1
        ));
    }

    /**
     * Grade import wizard step 2
     * 
     * URL: /faculty/grades/import/2
     */
    public function gradesImport2(Request $request, Application $app) {
        if (!$uploadedFile = Session::get('gw_uploaded_file')) {
            return $app->redirect($app->path('faculty.grades.import.1'));
        }

        $gradingSheet = new GradingSheet($uploadedFile);
        $sheets = array();

        foreach ($gradingSheet->getSheets() as $index => $sheet) {
            $sheets[$sheet] = $index;
        }

        $form = Form::create();

        $form->add('choices', Type\ChoiceType::class, array(
            'choices' => $sheets,
            'label' => ' ',
            'multiple' => true,
            'expanded' => true,
            'choice_attr' => function($val, $key, $index) {
                if (in_array($key, array('Master', 'Info Sheet', 'Setup', 'Class 1'))) {
                    return array(
                        'disabled' => true,
                        'title' => 'Sheet cannot be imported'
                    );
                }

                return array();
            }
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form['choices']->getData();

            if (count($data) > 3) {
                FlashBag::add('messages', 'danger>>>You can only select/import up to 3 sheets at a time.');
                return $app->redirect($app->path('faculty.grades.import.2'));
            }

            if ($previousData = Session::get('gw_selected_sheets')) {
                // Check if there are changes to sheet selection before busting the cache
                if ($previousData != $data) {
                    Session::remove('gw_contents');
                }
            }

            Session::set('gw_selected_sheets', $data);
            return $app->redirect($app->path('faculty.grades.import.3'));
        }

        return VieW::render('faculty/grades/import/2', array(
            'choose_form' => $form->createView(),
            'current_step' => 2
        ));
    }

    /**
     * Grade import wizard step 3
     * 
     * URL: /faculty/grades/import/3
     */
    public function gradesImport3(Request $request, Application $app) {
        if (!$uploadedFile = Session::get('gw_uploaded_file')) {
            return $app->redirect($app->path('faculty.grades.import.1'));
        }

        if (!$selectedSheets = Session::get('gw_selected_sheets')) {
            return $app->redirect($app->path('faculty.grades.import.2'));
        }

        /* Check if spreadsheet contents is cached in the session database
           Used remove the need to load the spreadsheet file again, thus saving time */
        if (!$contents = Session::get('gw_contents')) {
            set_time_limit(0);
            
            $gradingSheet = new GradingSheet($uploadedFile);
            $contents = $gradingSheet->getSheetsContents($selectedSheets);

            Session::set('gw_contents', $contents);
        }

        $form = Form::create();

        $form->add('_confirm', Type\HiddenType::class, array(
            'required' => false
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            Grade::import($contents);
            Session::set('gw_import_done', true);

            return $app->redirect($app->path('faculty.grades.import.4'));
        }

        return VieW::render('faculty/grades/import/3', array(
            'current_step' => 3,
            'confirm_form' => $form->createView(),
            'spreadsheet_contents' => $contents
        ));
    }

    /**
     * Grade import wizard step 4
     * 
     * URL: /faculty/grades/import/4
     */
    public function gradesImport4(Request $request, Application $app) {
        if (!$uploadedFile = Session::get('gw_uploaded_file')) {
            return $app->redirect($app->path('faculty.grades.import.1'));
        }

        if (!$selectedSheets = Session::get('gw_selected_sheets')) {
            return $app->redirect($app->path('faculty.grades.import.2'));
        }

        if (!$selectedSheets = Session::get('gw_import_done')) {
            return $app->redirect($app->path('faculty.grades.import.3'));
        }

        // cleanup
        Session::remove('gw_uploaded_file');
        Session::remove('gw_selected_sheets');
        Session::remove('gw_import_done');
        Session::remove('gw_contents');

        @unlink($uploadedFile);

        return VieW::render('faculty/grades/import/4', array(
            'current_step' => 4
        ));
    }
}
