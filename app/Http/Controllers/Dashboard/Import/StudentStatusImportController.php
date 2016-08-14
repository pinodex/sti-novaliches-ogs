<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Dashboard\Import;

use Cache;
use Session;
use Storage;
use Illuminate\Http\Request;
use Symfony\Component\Form\Extension\Core\Type;
use App\Extensions\Spreadsheet\StudentStatusSpreadsheet;
use App\Http\Controllers\Controller;
use App\Extensions\Form;
use App\Models\StudentStatus;

class StudentStatusImportController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('acl');
    }

    /**
     * Faculty import redirector
     * 
     * URL: /dashboard/import/students-status/
     */
    public function index() {
        return redirect()->route('dashboard.import.studentsstatus.stepOne');
    }

    /**
     * Student import wizard step 1
     * 
     * URL: /dashboard/import/students-status/upload
     */
    public function stepOne(Request $request) {
        if ($uploadedFile = Session::get('ss_uploaded_file')) {
            @unlink($uploadedFile);
        }

        // cleanup first
        Session::forget('ss_uploaded_file');
        Session::forget('ss_import_done');

        Cache::forget('status_sheet');

        $form = Form::create();

        $form->add('file', Type\FileType::class, [
            'label' => ' '
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $file = $form['file']->getData();

            if ($file->getError() != 0) {
                Session::flash('flash_message', 'danger>>>' . $file->getErrorMessage());
                
                return redirect()->route('dashboard.import.studentsstatus.stepOne');
            }

            $storageName = sprintf('/imports/students-status/%s.xlsx', uniqid(null, true));

            Storage::put($storageName, file_get_contents($form['file']->getData()->getPathName()));
            Session::put('ss_uploaded_file', storage_path('app' . $storageName));
            
            return redirect()->route('dashboard.import.studentsstatus.stepTwo');
        }

        return view('dashboard/import/students-status/1', [
            'upload_form'   => $form->createView(),
            'current_step'  => 1
        ]);
    }

    /**
     * Student import wizard step 2
     * 
     * URL: /dashboard/import/students-status/confirm
     */
    public function stepTwo(Request $request) {
        if (!$uploadedFile = Session::get('ss_uploaded_file')) {
            return redirect()->route('dashboard.import.studentsstatus.stepOne');
        }

        $spreadsheet = new StudentStatusSpreadsheet($uploadedFile);

        $form = Form::create();

        $form->add('_confirm', Type\HiddenType::class, [
            'required' => false
        ]);

        $form->add('purge', Type\CheckboxType::class, [
            'label'     => 'Purge and replace current students status',
            'required'  => false
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form['purge']->getData()) {
                StudentStatus::truncate();
            }

            $spreadsheet->importToDatabase();
            Session::put('ss_import_done', true);

            return redirect()->route('dashboard.import.studentsstatus.stepThree');
        }

        return view('dashboard/import/students-status/2', [
            'current_step'  => 2,
            'confirm_form'  => $form->createView()
        ]);
    }

    /**
     * Student import wizard step 3
     * 
     * URL: /dashboard/import/students-status/finish
     */
    public function stepThree() {
        if (!$uploadedFile = Session::get('ss_uploaded_file')) {
            return redirect()->route('dashboard.import.studentsstatus.stepOne');
        }

        if (!Session::get('ss_import_done')) {
            return redirect()->route('dashboard.import.studentsstatus.stepTwo');
        }

        // cleanup
        Session::forget('ss_uploaded_file');
        Session::forget('ss_import_done');

        Cache::forget('status_sheet');

        @unlink($uploadedFile);

        return view('dashboard/import/students-status/3', [
            'current_step' => 3
        ]);
    }
}
