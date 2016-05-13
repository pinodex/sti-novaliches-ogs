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
use App\Services\View;
use App\Services\Form;
use App\Services\Session;
use App\Services\FlashBag;
use App\Services\Parser\FacultySheet;
use App\Services\Importer\FacultyImporter;
use App\Controllers\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Route controller for faculty import pages
 */
class FacultyImportController extends Controller
{
    /**
     * Faculty import redirector
     * 
     * URL: /dashboard/faculty/import/
     */
    public function index(Application $app) {
        return $app->redirect($app->path('dashboard.faculty.import.1'));
    }

    /**
     * Grade import wizard step 1
     * 
     * URL: /dashboard/faculty/import/1
     */
    public function stepOne(Request $request, Application $app) {
        if ($uploadedFile = Session::get('fw_uploaded_file')) {
            @unlink($uploadedFile);
        }

        // cleanup first
        Session::remove('fw_uploaded_file');
        Session::remove('fw_selected_sheets');
        Session::remove('fw_import_done');
        Session::remove('fw_contents');

        $form = Form::create();

        $form->add('file', 'file', array(
            'label' => ' ',
            'constraints' => new Assert\File(array(
                'mimeTypesMessage' => 'Please upload a valid XLSX file',
                'mimeTypes' => array(
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                )
            ))
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        Form::handleFlashErrors('fw_upload_form', $form);
        
        if ($form->isValid()) {
            $file = $form['file']->getData();
            
            if ($file->getError() != 0) {
                FlashBag::add('messages', 'danger>>>' . $file->getErrorMessage());
                
                return $app->redirect($app->path('dashboard.faculty.import.1'));
            }
            
            $extension = $form['file']->getData()->guessExtension();
            
            $uploadedFile = $form['file']->getData()->move(ROOT . 'storage', sprintf('%s-%s.%s',
                date('Y-m-d-H-i-s'), uniqid(), $extension
            ));
            
            Session::set('fw_uploaded_file', $uploadedFile->getPathName());
            return $app->redirect($app->path('dashboard.faculty.import.2'));
        }
        
        return View::render('dashboard/faculty/import/1', array(
            'upload_form'   => $form->createView(),
            'current_step'  => 1
        ));
    }

    /**
     * Grade import wizard step 2
     * 
     * URL: /dashboard/faculty/import/2
     */
    public function stepTwo(Request $request, Application $app) {
        if (!$uploadedFile = Session::get('fw_uploaded_file')) {
            return $app->redirect($app->path('dashboard.faculty.import.1'));
        }

        $sheets = FacultySheet::parse($uploadedFile)->getSheets();
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

            if ($previousData = Session::get('fw_selected_sheets')) {
                // Check if there are changes to sheet selection before busting the cache
                if ($previousData != $data) {
                    Session::remove('fw_contents');
                }
            }

            Session::set('fw_selected_sheets', $data);
            return $app->redirect($app->path('dashboard.faculty.import.3'));
        }

        return View::render('dashboard/faculty/import/2', array(
            'choose_form'   => $form->createView(),
            'current_step'  => 2
        ));
    }

    /**
     * Grade import wizard step 3
     * 
     * URL: /dashboard/faculty/import/3
     */
    public function stepThree(Request $request, Application $app) {
        if (!$uploadedFile = Session::get('fw_uploaded_file')) {
            return $app->redirect($app->path('dashboard.faculty.import.1'));
        }

        if (!$selectedSheets = Session::get('fw_selected_sheets')) {
            return $app->redirect($app->path('dashboard.faculty.import.2'));
        }

        /* Check if spreadsheet contents is cached in the session database
           Used remove the need to load the spreadsheet file again, thus saving time */
        if (!$contents = Session::get('fw_contents')) {
            set_time_limit(0);
            
            $contents = FacultySheet::parse($uploadedFile)->getSheetsContent($selectedSheets);
            Session::set('fw_contents', $contents);
        }

        $form = Form::create();
        
        $form->add('_confirm', 'hidden', array(
            'required' => false
        ));
        
        $form = $form->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            FacultyImporter::import($contents);
            Session::set('fw_import_done', true);
            
            return $app->redirect($app->path('dashboard.faculty.import.4'));
        }

        return View::render('dashboard/faculty/import/3', array(
            'current_step'          => 3,
            'confirm_form'          => $form->createView(),
            'spreadsheet_contents'  => $contents
        ));
    }

    /**
     * Grade import wizard step 4
     * 
     * URL: /dashboard/faculty/import/4
     */
    public function stepFour(Application $app) {
        if (!$uploadedFile = Session::get('fw_uploaded_file')) {
            return $app->redirect($app->path('dashboard.faculty.import.1'));
        }

        if (!Session::get('fw_selected_sheets')) {
            return $app->redirect($app->path('dashboard.faculty.import.2'));
        }

        if (!Session::get('fw_import_done')) {
            return $app->redirect($app->path('dashboard.faculty.import.3'));
        }

        // cleanup
        Session::remove('fw_uploaded_file');
        Session::remove('fw_selected_sheets');
        Session::remove('fw_import_done');
        Session::remove('fw_contents');
        
        @unlink($uploadedFile);
        
        return View::render('dashboard/faculty/import/4', array(
            'current_step' => 4
        ));
    }
}
