<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers\Admin;

use Silex\Application;
use App\Models\Student;
use App\Services\View;
use App\Services\Form;
use App\Services\OmegaSheet;
use App\Services\Session\Session;
use App\Services\Session\FlashBag;
use App\Constraints as CustomAssert;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;

/**
 * Admin controller
 * 
 * Route controllers for admin pages (/admin/*)
 */
class ManageStudentController
{
    /**
     * Student import wizard index
     * 
     * URL: /admin/manage/student/import
     */
    public function studentImport(Application $app) {
        return $app->redirect($app->path('admin.manage.student.import.1'));
    }

    /**
     * Student import wizard step 1
     * 
     * URL: /admin/manage/student/import/1
     */
    public function studentImport1(Request $request, Application $app) {
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
            'attr' => array(
                'accept' => 'text/csv'
            )
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $file = $form['file']->getData();

            if ($file->getError() != 0) {
                FlashBag::add('messages', 'danger>>>' . $file->getErrorMessage());
                
                return $app->redirect($app->path('admin.manage.student.import.1'));
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
            
            return $app->redirect($app->path('admin.manage.student.import.2'));
        }

        return VieW::render('admin/manage/student/import/1', array(
            'upload_form' => $form->createView(),
            'current_step' => 1
        ));
    }

    /**
     * Student import wizard step 2
     * 
     * URL: /admin/manage/student/import/2
     */
    public function studentImport2(Request $request, Application $app) {
        if (!$uploadedFile = Session::get('sw_uploaded_file')) {
            return $app->redirect($app->path('admin.manage.student.import.1'));
        }

        $omegaSheet = new OmegaSheet($uploadedFile);
        $sheets = array();

        foreach ($omegaSheet->getSheets() as $index => $sheet) {
            $sheets[$sheet] = $index;
        }

        $form = Form::create();

        $form->add('choices', 'choice', array(
            'choices' => $sheets,
            'choices_as_values' => true,
            'label' => ' ',
            'multiple' => true,
            'expanded' => true
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form['choices']->getData();

            if (count($data) > 3) {
                FlashBag::add('messages', 'danger>>>You can only select/import up to 3 sheets at a time.');
                return $app->redirect($app->path('admin.manage.student.import.2'));
            }

            if ($previousData = Session::get('sw_selected_sheets')) {
                // Check if there are changes to sheet selection before busting the cache
                if ($previousData != $data) {
                    Session::remove('sw_contents');
                }
            }

            Session::set('sw_selected_sheets', $data);
            return $app->redirect($app->path('admin.manage.student.import.3'));
        }

        return VieW::render('admin/manage/student/import/2', array(
            'choose_form' => $form->createView(),
            'current_step' => 2
        ));
    }

    /**
     * Student import wizard step 3
     * 
     * URL: /admin/manage/student/import/3
     */
    public function studentImport3(Request $request, Application $app) {
        if (!$uploadedFile = Session::get('sw_uploaded_file')) {
            return $app->redirect($app->path('admin.manage.student.import.1'));
        }

        if (!$selectedSheets = Session::get('sw_selected_sheets')) {
            return $app->redirect($app->path('admin.manage.student.import.2'));
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

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            Student::import($contents);
            Session::set('sw_import_done', true);

            return $app->redirect($app->path('admin.manage.student.import.4'));
        }

        return VieW::render('admin/manage/student/import/3', array(
            'current_step' => 3,
            'confirm_form' => $form->createView(),
            'row_count' => $rowCount
        ));
    }

    /**
     * Student import wizard step 4
     * 
     * URL: /admin/manage/student/import/4
     */
    public function studentImport4(Application $app) {
        if (!$uploadedFile = Session::get('sw_uploaded_file')) {
            return $app->redirect($app->path('admin.manage.student.import.1'));
        }

        if (!Session::get('sw_selected_sheets')) {
            return $app->redirect($app->path('admin.manage.student.import.2'));
        }

        if (!Session::get('sw_import_done')) {
            return $app->redirect($app->path('admin.manage.student.import.3'));
        }

        // cleanup
        Session::remove('sw_uploaded_file');
        Session::remove('sw_selected_sheets');
        Session::remove('sw_import_done');
        Session::remove('sw_contents');

        @unlink($uploadedFile);

        return VieW::render('admin/manage/student/import/4', array(
            'current_step' => 4
        ));
    }

    /**
     * Add/Edit student
     * 
     * URL: /admin/manage/student/add
     * URL: /admin/manage/student/{id}/edit
     */
    public function editStudent(Request $request, Application $app, $id)
    {
        $mode = 'edit';

        if ($id && !$student = Student::find($id)) {
            FlashBag::add('messages', 'danger>>>Student not found');
            return $app->redirect($app->path('faculty.index'));
        }

        if (!$id) {
            $mode = 'add';
            $student = new Student();
        }

        $form = Form::create($student->toArray());

        $form->add('id', 'text', array(
            'label' => 'Student ID',

            'constraints' => array(
                new Assert\Regex(array(
                    'pattern' => '/([\d+]{3}-[\d+]{4}-[\d+]{4})|([\d+]{3})([\d+]{4})([\d+]{4})/',
                    'match' => true,
                    'message' => 'Invalid Student ID format'
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

            return $app->redirect($app->path('faculty.index'));
        }

        return View::render('admin/manage/student/' . $mode, array(
            'manage_form' => $form->createView(),
            'student' => $student->toArray()
        ));
    }

    /**
     * Delete student
     * 
     * URL: /admin/manage/student/{id}/delete
     */
    public function deleteStudent(Request $request, Application $app, $id)
    {
        if (!$student = Student::find($id)) {
            FlashBag::add('messages', 'danger>>>Student not found');

            return $app->redirect($app->path('faculty.index'));
        }

        $form = Form::create();
        $form->add('_confirm', 'hidden');

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $student->delete();

            FlashBag::add('messages', 'info>>>Student has been deleted');

            return $app->redirect($app->path('faculty.index'));
        }

        return View::render('admin/manage/student/delete', array(
            'manage_form' => $form->createView(),
            'student' => $student->toArray()
        ));
    }
}
