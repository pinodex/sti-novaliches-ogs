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
use App\Models\Department;
use App\Models\Section;
use App\Models\Faculty;
use App\Services\Auth;
use App\Services\View;
use App\Services\Form;
use App\Services\Session;
use App\Services\FacultySheet;
use App\Services\Session\FlashBag;
use App\Constraints as CustomAssert;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Route controller for faculty management pages
 */
class FacultiesController
{
    /**
     * Manage faculty accounts page
     * 
     * URL: /dashboard/faculties/
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
        
        $form->add('name', 'text', array(
            'label'     => 'Name',
            'required'  => false,
            'data'      => $request->query->get('name')
        ));

        $form = $form->getForm();
        $result = Faculty::search(null, $request->query->get('name'));

        return View::render('dashboard/faculties/index', array(
            'search_form'   => $form->createView(),
            'result'        => $result->toArray()
        ));
    }

    /**
     * Manage faculty summary accounts page
     * 
     * URL: /dashboard/faculties/summary
     */
    public function summary(Request $request)
    {
        $faculties = Faculty::all();

        return View::render('dashboard/faculties/summary', array(
            'faculties' => $faculties->toArray()
        ));
    }

    /**
     * View faculty account page
     * 
     * URL: /dashboard/faculties/{id}
     */
    public function view(Request $request, Application $app, $id)
    {
        if (!$faculty = Faculty::with('department')->find($id)) {
            FlashBag::add('messages', 'danger>>>Faculty account not found');
            return $app->redirect($app->path('dashboard.faculties'));
        }

        $user = Auth::user();

        if ($user->getRole() == 'head') {
            // Deny if the faculty and head does not belong to the same department
            // 
            if (!$faculty->department || $faculty->department->id != $user->getModel()->department->id) {
                FlashBag::add('messages', 'danger>>>This faculty is not in your department');

                return $app->redirect($app->path('dashboard.departments.view', array(
                    'id' => $user->getModel()->department->id
                )));
            }
        }

        return View::render('dashboard/faculties/view', array(
            'faculty'   => $faculty->toArray(),
            'logs'      => $faculty->submissionLogs->toArray()
        ));
    }

    /**
     * Edit faculty account page
     * 
     * URL: /dashboard/faculties/add
     * URL: /dashboard/faculties/{id}/edit
     */
    public function edit(Request $request, Application $app, $id)
    {
        $mode = 'add';
        $faculty = Faculty::findOrNew($id);

        if ($faculty->id != $id) {
            FlashBag::add('messages', 'danger>>>Faculty account not found');
            return $app->redirect($app->path('dashboard.faculties'));
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

            return $app->redirect($app->path('dashboard.faculties'));
        }

        return View::render('dashboard/faculties/' . $mode, array(
            'form'      => $form->createView(),
            'faculty'   => $faculty->toArray()
        ));
    }

    /**
     * Delete faculty account page
     * 
     * URL: /dashboard/faculties/{id}/delete
     */
    public function delete(Request $request, Application $app, $id)
    {
        if (!$faculty = Faculty::find($id)) {
            FlashBag::add('messages', 'danger>>>Faculty account not found');

            return $app->redirect($app->path('dashboard.faculties'));
        }

        $form = Form::create();
        $form->add('_confirm', 'hidden');

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $faculty->delete();

            FlashBag::add('messages', 'info>>>Faculty account has been deleted');

            return $app->redirect($app->path('dashboard.faculties'));
        }

        return View::render('dashboard/faculties/delete', array(
            'form'      => $form->createView(),
            'faculty'   => $faculty->toArray()
        ));
    }

    public function import(Application $app) {
        return $app->redirect($app->path('dashboard.faculties.import.1'));
    }

    /**
     * Grade import wizard step 1
     * 
     * URL: /dashboard/faculties/import/1
     */
    public function import1(Request $request, Application $app) {
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
                
                return $app->redirect($app->path('dashboard.faculties.import.1'));
            }
            
            $extension = $form['file']->getData()->guessExtension();
            
            $uploadedFile = $form['file']->getData()->move(ROOT . 'storage', sprintf('%s-%s.%s',
                date('Y-m-d-H-i-s'), uniqid(), $extension
            ));
            
            Session::set('fw_uploaded_file', $uploadedFile->getPathName());
            
            // Check if it's a valid grading sheet
            $sheets = (new FacultySheet($uploadedFile))->getSheets();     
            return $app->redirect($app->path('dashboard.faculties.import.2'));
        }
        
        return View::render('dashboard/faculties/import/1', array(
            'upload_form'   => $form->createView(),
            'current_step'  => 1
        ));
    }

    /**
     * Grade import wizard step 2
     * 
     * URL: /dashboard/faculties/import/2
     */
    public function import2(Request $request, Application $app) {
        if (!$uploadedFile = Session::get('fw_uploaded_file')) {
            return $app->redirect($app->path('dashboard.faculties.import.1'));
        }

        $facultySheet = new FacultySheet($uploadedFile);
        $sheets = array();
        
        foreach ($facultySheet->getSheets() as $index => $sheet) {
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

            if ($previousData = Session::get('fw_selected_sheets')) {
                // Check if there are changes to sheet selection before busting the cache
                if ($previousData != $data) {
                    Session::remove('fw_contents');
                }
            }

            Session::set('fw_selected_sheets', $data);
            return $app->redirect($app->path('dashboard.faculties.import.3'));
        }

        return View::render('dashboard/faculties/import/2', array(
            'choose_form'   => $form->createView(),
            'current_step'  => 2
        ));
    }

    /**
     * Grade import wizard step 3
     * 
     * URL: /dashboard/faculties/import/3
     */
    public function import3(Request $request, Application $app) {
        if (!$uploadedFile = Session::get('fw_uploaded_file')) {
            return $app->redirect($app->path('dashboard.faculties.import.1'));
        }

        if (!$selectedSheets = Session::get('fw_selected_sheets')) {
            return $app->redirect($app->path('dashboard.faculties.import.2'));
        }

        /* Check if spreadsheet contents is cached in the session database
           Used remove the need to load the spreadsheet file again, thus saving time */
        if (!$contents = Session::get('fw_contents')) {
            set_time_limit(0);
            
            $facultySheet = new FacultySheet($uploadedFile);
            $contents = $facultySheet->getSheetsContents($selectedSheets);
            
            Session::set('fw_contents', $contents);
        }

        $form = Form::create();
        
        $form->add('_confirm', 'hidden', array(
            'required' => false
        ));
        
        $form = $form->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            Faculty::import($contents);
            Session::set('fw_import_done', true);
            
            return $app->redirect($app->path('dashboard.faculties.import.4'));
        }

        return View::render('dashboard/faculties/import/3', array(
            'current_step'          => 3,
            'confirm_form'          => $form->createView(),
            'spreadsheet_contents'  => $contents
        ));
    }

    /**
     * Grade import wizard step 4
     * 
     * URL: /dashboard/faculties/import/4
     */
    public function import4(Application $app) {
        if (!$uploadedFile = Session::get('fw_uploaded_file')) {
            return $app->redirect($app->path('dashboard.faculties.import.1'));
        }

        if (!Session::get('fw_selected_sheets')) {
            return $app->redirect($app->path('dashboard.faculties.import.2'));
        }

        if (!Session::get('fw_import_done')) {
            return $app->redirect($app->path('dashboard.faculties.import.3'));
        }

        // cleanup
        Session::remove('fw_uploaded_file');
        Session::remove('fw_selected_sheets');
        Session::remove('fw_import_done');
        Session::remove('fw_contents');
        
        @unlink($uploadedFile);
        
        return View::render('dashboard/faculties/import/4', array(
            'current_step' => 4
        ));
    }
}
