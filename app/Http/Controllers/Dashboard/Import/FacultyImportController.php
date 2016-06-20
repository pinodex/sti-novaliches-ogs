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
use App\Extensions\Parser\FacultySheet;
use App\Http\Controllers\Controller;
use App\Extensions\Form;

class FacultyImportController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Faculty import redirector
     * 
     * URL: /dashboard/faculty/import/
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
        Session::forget('fw_selected_sheets');
        Session::forget('fw_import_done');

        Cache::forget('faculty_sheet');

        $form = Form::create();

        $form->add('file', Type\FileType::class, [
            'label' => ' ',
            'constraints' => new Assert\File([
                'mimeTypesMessage' => 'Please upload a valid XLSX file',
                'mimeTypes' => array(
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                )
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

            $storageName = sprintf('/imports/faculty/%s.%s', uniqid(null, true), $file->guessExtension());

            Storage::put($storageName, file_get_contents($file->getPathname()));
            Session::put('fw_uploaded_file', storage_path() . '/app' . $storageName);

            return redirect()->route('dashboard.import.faculty.stepTwo');
        }
        
        return view('dashboard/import/faculty/1', array(
            'upload_form'   => $form->createView(),
            'current_step'  => 1
        ));
    }

    /**
     * Grade import wizard step 2
     * 
     * URL: /dashboard/faculty/import/2
     */
    public function stepTwo(Request $request) {
        if (!$uploadedFile = Session::get('fw_uploaded_file')) {
            return redirect()->route('dashboard.import.faculty.stepOne');
        }

        $sheets = FacultySheet::parse($uploadedFile)->getSheets();
        $form = Form::create();
        
        $form->add('choices', Type\ChoiceType::class, array(
            'choices'   => array_flip($sheets),
            'label'     => 'Sheet Selection',
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
                    Cache::forget('faculty_sheet');
                }
            }

            Session::put('fw_selected_sheets', $data);
            return redirect()->route('dashboard.import.faculty.stepThree');
        }

        return view('dashboard/import/faculty/2', array(
            'choose_form'   => $form->createView(),
            'current_step'  => 2
        ));
    }
}
