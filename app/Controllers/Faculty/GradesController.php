<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers\Faculty;

use Silex\Application;
use App\Models\Grade;
use App\Services\Form;
use App\Services\View;
use App\Services\GradingSheet;
use App\Services\Session\Session;
use App\Services\Session\FlashBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Faculty maion controller
 * 
 * Route controllers for /faculty/
 */
class GradesController
{
    /**
     * Grade import wizard index
     * 
     * URL: /faculty/grades/import
     */
    public function index(Application $app) {
        return $app->redirect($app->path('faculty.grades.import.1'));
    }

    /**
     * Grade import wizard step 1
     * 
     * URL: /faculty/grades/import/1
     */
    public function import1(Request $request, Application $app) {
        if ($uploadedFile = Session::get('gw_uploaded_file')) {
            @unlink($uploadedFile);
        }

        // cleanup first
        Session::remove('gw_uploaded_file');
        Session::remove('gw_selected_sheets');
        Session::remove('gw_import_done');
        Session::remove('gw_contents');

        $form = Form::create();

        $form->add('file', 'file', array(
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

        Form::handleFlashErrors('gw_upload_form', $form);

        if ($form->isValid()) {
            $file = $form['file']->getData();

            if ($file->getError() != 0) {
                FlashBag::add('messages', 'danger>>>' . $file->getErrorMessage());
                
                return $app->redirect($app->path('faculty.grades.import.1'));
            }
            
            $extension = $form['file']->getData()->guessExtension();
            
            $uploadedFile = $form['file']->getData()->move(ROOT . 'storage', sprintf('%s-%s.%s',
                date('Y-m-d-H-i-s'), uniqid(), $extension
            ));

            Session::set('gw_uploaded_file', $uploadedFile->getPathName());

            // Check if it's a valid grading sheet
            $sheets = (new GradingSheet($uploadedFile))->getSheets();

            if (!in_array('Master', $sheets) &&
                !in_array('Info Sheet', $sheets) &&
                !in_array('Setup', $sheets)) {

                Form::flashError('gw_upload_form', 'Please upload a valid grading sheet file.');
                return $app->redirect($app->path('faculty.grades.import.1'));
            }
            
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
    public function import2(Request $request, Application $app) {
        if (!$uploadedFile = Session::get('gw_uploaded_file')) {
            return $app->redirect($app->path('faculty.grades.import.1'));
        }

        $gradingSheet = new GradingSheet($uploadedFile);
        $sheets = array();

        foreach ($gradingSheet->getSheets() as $index => $sheet) {
            $sheets[$sheet] = $index;
        }

        $form = Form::create();

        $form->add('choices', 'choice', array(
            'choices' => $sheets,
            'choices_as_values' => true,
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
    public function import3(Request $request, Application $app) {
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
        $form->add('_confirm', 'hidden', array(
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
    public function import4(Application $app) {
        if (!$uploadedFile = Session::get('gw_uploaded_file')) {
            return $app->redirect($app->path('faculty.grades.import.1'));
        }

        if (!Session::get('gw_selected_sheets')) {
            return $app->redirect($app->path('faculty.grades.import.2'));
        }

        if (!Session::get('gw_import_done')) {
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
