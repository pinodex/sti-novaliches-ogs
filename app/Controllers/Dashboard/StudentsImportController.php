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
use App\Models\Student;
use App\Services\View;
use App\Services\Form;
use App\Services\Cache;
use App\Services\Session;
use App\Services\FlashBag;
use App\Controllers\Controller;
use App\Components\Parser\StudentSheet;
use App\Components\Importer\StudentImporter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

/**
 * Route controller for student import pages
 */
class StudentsImportController extends Controller
{
    /**
     * Student import wizard index
     * 
     * URL: /dashboard/students/import
     */
    public function index(Application $app) {
        return $app->redirect($app->path('dashboard.students.import.1'));
    }

    /**
     * Student import wizard step 1
     * 
     * URL: /dashboard/students/import/1
     */
    public function stepOne(Request $request, Application $app) {
        if ($uploadedFile = Session::get('sw_uploaded_file')) {
            @unlink($uploadedFile);
        }

        // cleanup first
        Session::remove('sw_uploaded_file');
        Session::remove('sw_selected_sheets');
        Session::remove('sw_import_done');

        Cache::getInstance()->remove('omega_sheet');

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
    public function stepTwo(Request $request, Application $app) {
        if (!$uploadedFile = Session::get('sw_uploaded_file')) {
            return $app->redirect($app->path('dashboard.students.import.1'));
        }

        /* Check if spreadsheet contents is cached in the session database
           Used remove the need to load the spreadsheet file again, thus saving time */
        if (!$contents = Cache::getInstance()->get('omega_sheet')) {
            set_time_limit(0);
            
            $contents = StudentSheet::parse($uploadedFile)->getSheetContents(0);
            Cache::getInstance()->put('omega_sheet', $contents);
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

            StudentImporter::import($contents);
            Session::set('sw_import_done', true);

            return $app->redirect($app->path('dashboard.students.import.3'));
        }

        return View::render('dashboard/students/import/2', array(
            'current_step'  => 2,
            'confirm_form'  => $form->createView(),
            'row_count'     => count($contents)
        ));
    }

    /**
     * Student import wizard step 3
     * 
     * URL: /dashboard/students/import/3
     */
    public function stepThree(Application $app) {
        if (!$uploadedFile = Session::get('sw_uploaded_file')) {
            return $app->redirect($app->path('dashboard.students.import.1'));
        }

        if (!Session::get('sw_import_done')) {
            return $app->redirect($app->path('dashboard.students.import.2'));
        }

        // cleanup
        Session::remove('sw_uploaded_file');
        Session::remove('sw_import_done');

        Cache::getInstance()->remove('omega_sheet');

        @unlink($uploadedFile);

        return View::render('dashboard/students/import/3', array(
            'current_step' => 3
        ));
    }
}
