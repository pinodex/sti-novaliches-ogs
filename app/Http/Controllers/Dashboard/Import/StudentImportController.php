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
use Symfony\Component\Validator\Constraints as Assert;
use App\Extensions\Constraints as CustomAssert;
use App\Extensions\Importer\StudentImporter;
use App\Extensions\Parser\StudentSheet;
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
     * URL: /dashboard/import/students/1
     */
    public function stepOne(Request $request) {
        if ($uploadedFile = Session::get('sw_uploaded_file')) {
            @unlink($uploadedFile);
        }

        // cleanup first
        Session::forget('sw_uploaded_file');
        Session::forget('sw_selected_sheets');
        Session::forget('sw_import_done');

        Cache::forget('omega_sheet');

        $form = Form::create();

        $form->add('file', Type\FileType::class, [
            'attr'  => ['accept' => 'text/csv'],
            'label' => ' '
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $file = $form['file']->getData();

            if ($file->getError() != 0) {
                Session::flash('flash_message', 'danger>>>' . $file->getErrorMessage());
                
                return redirect()->route('dashboard.import.students.stepOne');
            }

            $storageName = sprintf('/imports/students/%s.%s', uniqid(null, true), $file->guessExtension());

            /**
             * We need to convert it to utf-8 to store special and accented characters
             */
            
            $contents = file_get_contents($form['file']->getData()->getPathName());

            Storage::put($storageName, mb_convert_encoding($contents, 'UTF-8', 'pass'));
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
     * URL: /dashboard/import/students/2
     */
    public function stepTwo(Request $request) {
        if (!$uploadedFile = Session::get('sw_uploaded_file')) {
            return redirect()->route('dashboard.import.students.stepOne');
        }

        /* Check if spreadsheet contents is cached in the session database
           Used remove the need to load the spreadsheet file again, thus saving time */
        if (!$contents = Cache::get('omega_sheet')) {
            set_time_limit(0);
            
            $contents = StudentSheet::parse($uploadedFile)->getSheetContents(0);
            Cache::put('omega_sheet', $contents, 60);
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

            StudentImporter::import($contents);
            Session::put('sw_import_done', true);

            return redirect()->route('dashboard.import.students.stepThree');
        }

        return view('dashboard/import/students/2', [
            'current_step'  => 2,
            'confirm_form'  => $form->createView(),
            'row_count'     => count($contents)
        ]);
    }

    /**
     * Student import wizard step 3
     * 
     * URL: /dashboard/import/students/3
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

        Cache::forget('omega_sheet');

        @unlink($uploadedFile);

        return view('dashboard/import/students/3', [
            'current_step' => 3
        ]);
    }
}
