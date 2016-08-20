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
use App\Extensions\Spreadsheet\StudentSpreadsheet;
use App\Http\Controllers\Controller;
use App\Extensions\Form;
use App\Models\Student;

class StudentImportController extends Controller
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
     * URL: /dashboard/import/students/
     */
    public function index() {
        return redirect()->route('dashboard.import.students.stepOne');
    }

    /**
     * Student import wizard step 1
     * 
     * URL: /dashboard/import/students/upload
     */
    public function stepOne(Request $request) {
        if ($uploadedFile = Session::get('sw_uploaded_file')) {
            @unlink($uploadedFile);
        }

        // cleanup first
        Session::forget('sw_uploaded_file');
        Session::forget('sw_import_done');

        Cache::forget('omega_sheet_count');

        $form = Form::create();

        $form->add('file', Type\FileType::class, [
            'label' => 'Student List File'
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $file = $form['file']->getData();

            if ($file->getError() != 0) {
                Session::flash('flash_message', 'danger>>>' . $file->getErrorMessage());
                
                return redirect()->route('dashboard.import.students.stepOne');
            }

            $storageName = sprintf('/imports/students/%s.xlsx', uniqid(null, true));

            Storage::put($storageName, file_get_contents($form['file']->getData()->getPathName()));
            Session::put('sw_uploaded_file', storage_path('app' . $storageName));
            
            return redirect()->route('dashboard.import.students.stepTwo');
        }

        return view('dashboard/import/students/1', [
            'upload_form'   => $form->createView(),
            'current_step'  => 1
        ]);
    }

    /**
     * Student import wizard step 2
     * 
     * URL: /dashboard/import/students/confirm
     */
    public function stepTwo(Request $request) {
        if (!$uploadedFile = Session::get('sw_uploaded_file')) {
            return redirect()->route('dashboard.import.students.stepOne');
        }

        $spreadsheet = new StudentSpreadsheet($uploadedFile);

        if (!$count = Cache::get('omega_sheet_count')) {
            set_time_limit(0);
            
            $count = count($spreadsheet->getParsedContents());
            Cache::put('omega_sheet_count', $count, 60);
        }

        $form = Form::create();

        $form->add('_confirm', Type\HiddenType::class, [
            'required' => false
        ]);

        $form->add('purge', Type\CheckboxType::class, [
            'label'     => 'Purge student master list before import',
            'required'  => false
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form['purge']->getData()) {
                Student::truncate();
            }

            $spreadsheet->importToDatabase();
            Session::put('sw_import_done', true);

            return redirect()->route('dashboard.import.students.stepThree');
        }

        return view('dashboard/import/students/2', [
            'current_step'  => 2,
            'confirm_form'  => $form->createView(),
            'row_count'     => $count
        ]);
    }

    /**
     * Student import wizard step 3
     * 
     * URL: /dashboard/import/students/finish
     */
    public function stepThree() {
        if (!$uploadedFile = Session::get('sw_uploaded_file')) {
            return redirect()->route('dashboard.import.students.stepOne');
        }

        if (!Session::get('sw_import_done')) {
            return redirect()->route('dashboard.import.students.stepTwo');
        }

        // cleanup
        Session::forget('sw_uploaded_file');
        Session::forget('sw_import_done');

        Cache::forget('omega_sheet_count');

        @unlink($uploadedFile);

        return view('dashboard/import/students/3', [
            'current_step' => 3
        ]);
    }
}
