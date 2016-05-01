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
use App\Services\Auth;
use App\Services\View;
use App\Services\Form;
use App\Services\Helper;
use App\Services\Session;
use App\Services\GradingSheet;
use App\Services\Session\FlashBag;
use App\Constraints as CustomAssert;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;

/**
 * Route controller for grade pages
 */
class GradesController
{
    /**
     * Grades page index
     * 
     * URL: /dashboard/grades/
     */
    public function index(Request $request)
    {
        return View::render('dashboard/grades/index');
    }

    public function import(Application $app) {
        return $app->redirect($app->path('dashboard.grades.import.1'));
    }

    /**
     * Grade import wizard step 1
     * 
     * URL: /dashboard/grades/import/1
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
                
                return $app->redirect($app->path('dashboard.grades.import.1'));
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
                return $app->redirect($app->path('dashboard.grades.import.1'));
            }
            
            return $app->redirect($app->path('dashboard.grades.import.2'));
        }
        
        return View::render('dashboard/grades/import/1', array(
            'upload_form'   => $form->createView(),
            'current_step'  => 1
        ));
    }

    /**
     * Grade import wizard step 2
     * 
     * URL: /dashboard/grades/import/2
     */
    public function import2(Request $request, Application $app) {
        if (!$uploadedFile = Session::get('gw_uploaded_file')) {
            return $app->redirect($app->path('dashboard.grades.import.1'));
        }

        $gradingSheet = new GradingSheet($uploadedFile);
        $sheets = array();
        
        foreach ($gradingSheet->getSheets() as $index => $sheet) {
            if (in_array($sheet, array('Master', 'Info Sheet', 'Setup', 'Class 1'))) {
                continue;
            }

            $sheets[$index] = $sheet;
        }
        
        $form = Form::create();
        
        $form->add('choices', 'choice', array(
            'choices'   => $sheets,
            'label'     => ' ',
            'multiple'  => true,
            'expanded'  => true
        ));

        $form = $form->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form['choices']->getData();

            if ($previousData = Session::get('gw_selected_sheets')) {
                // Check if there are changes to sheet selection before busting the cache
                if ($previousData != $data) {
                    Session::remove('gw_contents');
                }
            }

            Session::set('gw_selected_sheets', $data);
            return $app->redirect($app->path('dashboard.grades.import.3'));
        }

        return View::render('dashboard/grades/import/2', array(
            'choose_form'   => $form->createView(),
            'current_step'  => 2
        ));
    }

    /**
     * Grade import wizard step 3
     * 
     * URL: /dashboard/grades/import/3
     */
    public function import3(Request $request, Application $app) {
        if (!$uploadedFile = Session::get('gw_uploaded_file')) {
            return $app->redirect($app->path('dashboard.grades.import.1'));
        }

        if (!$selectedSheets = Session::get('gw_selected_sheets')) {
            return $app->redirect($app->path('dashboard.grades.import.2'));
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
            
            return $app->redirect($app->path('dashboard.grades.import.4'));
        }

        return View::render('dashboard/grades/import/3', array(
            'current_step'          => 3,
            'confirm_form'          => $form->createView(),
            'spreadsheet_contents'  => $contents
        ));
    }

    /**
     * Grade import wizard step 4
     * 
     * URL: /dashboard/grades/import/4
     */
    public function import4(Application $app) {
        if (!$uploadedFile = Session::get('gw_uploaded_file')) {
            return $app->redirect($app->path('dashboard.grades.import.1'));
        }

        if (!Session::get('gw_selected_sheets')) {
            return $app->redirect($app->path('dashboard.grades.import.2'));
        }

        if (!Session::get('gw_import_done')) {
            return $app->redirect($app->path('dashboard.grades.import.3'));
        }

        $user = Auth::user();

        if ($user->getRole() == 'faculty') {
            $user->getModel()->last_grade_submission_at = date('Y-m-d H:i:s');
            $user->getModel()->save();
        }

        // cleanup
        Session::remove('gw_uploaded_file');
        Session::remove('gw_selected_sheets');
        Session::remove('gw_import_done');
        Session::remove('gw_contents');
        
        @unlink($uploadedFile);
        
        return View::render('dashboard/grades/import/4', array(
            'current_step' => 4
        ));
    }
}
