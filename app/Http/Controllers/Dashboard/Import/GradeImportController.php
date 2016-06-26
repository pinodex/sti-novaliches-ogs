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
use App\Extensions\Importer\GradeImporter;
use App\Extensions\Parser\GradingSheet;
use App\Http\Controllers\Controller;
use App\Extensions\Form;
use App\Jobs\ImportJob;
use App\Jobs\ParallelJob;
use App\Jobs\DeleteFileJob;
use App\Jobs\DeliverGradesJob;

class GradeImportController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('acl');
    }

    /**
     * Grade import wizard redirector
     * 
     * URL: /dashboard/import/grades
     */
    public function index() {
        return redirect()->route('dashboard.import.grades.stepOne');
    }

    /**
     * Grade import wizard step 1
     * 
     * URL: /dashboard/import/grades/1
     */
    public function stepOne(Request $request) {
        if ($uploadedFile = Session::get('gw_uploaded_file')) {
            @unlink($uploadedFile);
        }

        Session::forget('gw_uploaded_file');
        Session::forget('gw_selected_sheets');
        Session::forget('gw_import_done');

        Cache::forget('grading_sheet');

        $form = Form::create();

        $form->add('file', Type\FileType::class, [
            'label' => ' ',
            'constraints' => new Assert\File([
                'mimeTypesMessage' => 'Please upload a valid XLSX/XLSM file',
                'mimeTypes' => [
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel.sheet.macroEnabled.12'
                ]
            ])
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $file = $form['file']->getData();
            
            if ($file->getError() != 0) {
                Session::flash('flash_message', 'danger>>>' . $file->getErrorMessage());

                return redirect()->route('dashboard.import.grades.stepOne');
            }

            $storageName = sprintf('/imports/grades/%s.%s', uniqid(null, true), $file->guessExtension());

            Storage::put($storageName, file_get_contents($file->getPathname()));
            
            Session::put('gw_uploaded_file', storage_path('app' . $storageName));
            Session::put('gw_metadata', [
                'mimeType' => $file->getMimeType(),
                'originalName' => $file->getClientOriginalName()
            ]);
            
            // Check if it's a valid grading sheet
            $sheets = GradingSheet::parse(storage_path('app' . $storageName))->getSheets();
            
            if (!in_array('Master', $sheets) && !in_array('Info Sheet', $sheets) && !in_array('Setup', $sheets)) {
                Session::flash('flash_message', 'danger>>>' . 'Please upload a valid grading sheet file.');
                
                return redirect()->route('dashboard.import.grades.stepOne');
            }
            
            return redirect()->route('dashboard.import.grades.stepTwo');
        }
        
        return view('dashboard/import/grades/1', [
            'upload_form'   => $form->createView(),
            'current_step'  => 1
        ]);
    }

    /**
     * Grade import wizard step 2
     * 
     * URL: /dashboard/import/grades/2
     */
    public function stepTwo(Request $request) {
        if (!$uploadedFile = Session::get('gw_uploaded_file')) {
            return redirect()->route('dashboard.import.grades.stepOne');
        }

        $gradingSheet = GradingSheet::parse($uploadedFile);
        $sheets = [];
        
        foreach ($gradingSheet->getSheets() as $index => $sheet) {
            if (in_array($sheet, ['Master', 'Info Sheet', 'Setup', 'Class 1'])) {
                continue;
            }

            $sheets[$index] = $sheet;
        }
        
        $form = Form::create();
        
        $form->add('choices', Type\ChoiceType::class, [
            'choices'   => array_flip($sheets),
            'label'     => 'Sheet selection',
            'multiple'  => true,
            'expanded'  => true
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form['choices']->getData();

            if ($previousData = Session::get('gw_selected_sheets')) {
                // Check if there are changes to sheet selection before busting the cache
                if ($previousData != $data) {
                    Cache::forget('grading_sheet');
                }
            }

            Session::put('gw_selected_sheets', $data);

            return redirect()->route('dashboard.import.grades.stepThree');
        }

        return view('dashboard/import/grades/2', [
            'choose_form'   => $form->createView(),
            'current_step'  => 2
        ]);
    }

    /**
     * Grade import wizard step 3
     * 
     * URL: /dashboard/import/grades/3
     */
    public function stepThree(Request $request) {
        if (!$uploadedFile = Session::get('gw_uploaded_file')) {
            return redirect()->route('dashboard.import.grades.stepOne');
        }

        if (!$selectedSheets = Session::get('gw_selected_sheets')) {
            return redirect()->route('dashboard.import.grades.stepTwo');
        }

        $metadata = Session::get('gw_metadata');
        $parser = GradingSheet::parse($uploadedFile);

        if (!$contents = Cache::get('grading_sheet')) {
            set_time_limit(0);
            
            $contents = $parser->getSheetsContent($selectedSheets);
            
            Cache::put('grading_sheet', $contents, 60);
        }

        $form = Form::create();
        
        $form->add('_confirm', Type\HiddenType::class, [
            'required' => false
        ]);
        
        $form = $form->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $importer = null;

            if ($this->isRole('faculty')) {
                $this->user->addSubmissionLogEntry();
                $importer = $this->user;
            }
            
            $this->dispatch(new ParallelJob([
                new ImportJob(GradingSheet::class, GradeImporter::class, $uploadedFile, $selectedSheets, [$importer]),
                new DeliverGradesJob($uploadedFile, $metadata),
                new DeleteFileJob($uploadedFile)
            ]));

            Session::put('gw_import_done', true);

            return redirect()->route('dashboard.import.grades.stepFour');
        }

        return view('dashboard/import/grades/3', [
            'current_step'          => 3,
            'confirm_form'          => $form->createView(),
            'spreadsheet_contents'  => $contents
        ]);
    }

    /**
     * Grade import wizard step 4
     * 
     * URL: /dashboard/import/grades/4
     */
    public function stepFour() {
        if (!$uploadedFile = Session::get('gw_uploaded_file')) {
            return redirect()->route('dashboard.import.grades.stepOne');
        }

        if (!Session::get('gw_selected_sheets')) {
            return redirect()->route('dashboard.import.grades.stepTwo');
        }

        if (!Session::get('gw_import_done')) {
            return redirect()->route('dashboard.import.grades.stepThree');
        }

        // cleanup
        Session::forget('gw_uploaded_file');
        Session::forget('gw_selected_sheets');
        Session::forget('gw_import_done');

        Cache::forget('grading_sheet');
        
        return view('dashboard/import/grades/4', [
            'current_step' => 4
        ]);
    }
}
