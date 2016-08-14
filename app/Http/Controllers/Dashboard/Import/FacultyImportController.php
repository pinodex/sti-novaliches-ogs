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
use App\Extensions\Spreadsheet\FacultySpreadsheet;
use App\Http\Controllers\Controller;
use App\Extensions\Form;

class FacultyImportController extends Controller
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
     * URL: /dashboard/import/faculty/
     */
    public function index() {
        return redirect()->route('dashboard.import.faculty.stepOne');
    }

    /**
     * Grade import wizard step 1
     * 
     * URL: /dashboard/import/faculty/upload
     */
    public function stepOne(Request $request) {
        if ($uploadedFile = Session::get('fw_uploaded_file')) {
            @unlink($uploadedFile);
        }

        // cleanup first
        Session::forget('fw_uploaded_file');
        Session::forget('fw_import_done');

        Cache::forget('faculty_sheet');

        $form = Form::create();

        $form->add('file', Type\FileType::class, [
            'label' => ' ',
            'constraints' => new Assert\File([
                'mimeTypesMessage' => 'Please upload a valid XLSX file',
                'mimeTypes' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            ])
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        Form::handleFlashErrors('fw_upload_form', $form);
        
        if ($form->isValid()) {
            $file = $form['file']->getData();
            
            if ($file->getError() != 0) {
                Session::flash('flash_message', 'danger>>>' . $file->getErrorMessage());
                
                return redirect()->route('dashboard.import.faculty.stepOne');
            }

            $storageName = sprintf('/imports/faculty/%s.xlsx', uniqid(null, true));

            Storage::put($storageName, file_get_contents($file->getPathname()));
            Session::put('fw_uploaded_file', storage_path('app' . $storageName));

            return redirect()->route('dashboard.import.faculty.stepTwo');
        }
        
        return view('dashboard/import/faculty/1', [
            'upload_form'   => $form->createView(),
            'current_step'  => 1
        ]);
    }

    /**
     * Grade import wizard step 2
     * 
     * URL: /dashboard/import/faculty/confirm
     */
    public function stepTwo(Request $request) {
        if (!$uploadedFile = Session::get('fw_uploaded_file')) {
            return redirect()->route('dashboard.import.faculty.stepOne');
        }

        $spreadsheet = new FacultySpreadsheet($uploadedFile);

        if (!$contents = Cache::get('faculty_sheet')) {
            set_time_limit(0);
            
            $contents = $spreadsheet->getParsedContents();
            Cache::put('faculty_sheet', $contents, 60);
        }

        $form = Form::create();
        
        $form->add('_confirm', Type\HiddenType::class, [
            'required' => false
        ]);
        
        $form = $form->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $spreadsheet->importToDatabase();
            Session::put('fw_import_done', true);
            
            return redirect()->route('dashboard.import.faculty.stepThree');
        }

        return view('dashboard/import/faculty/2', [
            'current_step'  => 2,
            'confirm_form'  => $form->createView(),
            'contents'      => $contents
        ]);
    }

    /**
     * Grade import wizard step 3
     * 
     * URL: /dashboard/import/faculty/finish
     */
    public function stepThree() {
        if (!$uploadedFile = Session::get('fw_uploaded_file')) {
            return redirect()->route('dashboard.import.faculty.stepOne');
        }

        if (!Session::get('fw_import_done')) {
            return redirect()->route('dashboard.import.faculty.stepTwo');
        }

        // cleanup
        Session::forget('fw_uploaded_file');
        Session::forget('fw_selected_sheets');
        Session::forget('fw_import_done');

        Cache::forget('faculty_sheet');
        
        @unlink($uploadedFile);
        
        return view('dashboard/import/faculty/3', [
            'current_step' => 3
        ]);
    }
}
