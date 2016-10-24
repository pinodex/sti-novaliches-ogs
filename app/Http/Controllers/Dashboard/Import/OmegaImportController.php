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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Extensions\Spreadsheet\SpreadsheetFactory;
use App\Extensions\Spreadsheet\GradeSpreadsheet;
use App\Extensions\Spreadsheet\OmegaSpreadsheet;
use App\Http\Controllers\Controller;
use App\Jobs\ParallelJob;
use App\Jobs\DeleteFileJob;
use App\Jobs\SendEmailJob;
use App\Extensions\Form;
use App\Extensions\SgrReporter;
use App\Extensions\Email\GradeDelivery;

class OmegaImportController extends Controller
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
     * URL: /dashboard/import/omega
     */
    public function index() {
        return redirect()->route('dashboard.import.omega.stepOne');
    }

    /**
     * Grade import wizard step 1
     * 
     * URL: /dashboard/import/omega/upload
     */
    public function stepOne(Request $request) {
        Session::forget('om_file');
        Session::forget('om_import_done');

        $form = Form::create();

        $form->add('omega', Type\FileType::class, [
            'label'         => 'OMEGA File',
            'attr'          => ['accept' => '.xlsx']
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $files = $form->getData();
            
            if ($files['omega']->getError() > 0) {
                Session::flash('flash_message', 'danger>>>' . $files['omega']->getErrorMessage());

                return redirect()->route('dashboard.import.omega.stepOne');
            }

            $id = uniqid(null, true);
            $omegaTarget = sprintf('/imports/grades/%s-global-omega.xlsx', $id);

            Storage::put($omegaTarget, file_get_contents($files['omega']->getPathname()));
            
            Session::put('om_file', storage_path('app' . $omegaTarget));
            
            return redirect()->route('dashboard.import.omega.stepTwo');
        }
        
        return view('dashboard/import/omega/1', [
            'upload_form'   => $form->createView(),
            'current_step'  => 1,
            'invalid'       => $request->query->get('invalid')
        ]);
    }

    /**
     * Grade import wizard step 2
     * 
     * URL: /dashboard/import/omega/confirm
     */
    public function stepTwo(Request $request) {
        if (!$file = Session::get('om_file')) {
            return redirect()->route('dashboard.import.omega.stepOne');
        }

        if (Session::get('om_import_done')) {
            Session::flash('flash_message', 'info>>>Your OMEGA was already imported.');

            return redirect()->route('dashboard.import.omega.stepThree');
        }

        $omega = new OmegaSpreadsheet($file);

        if (!$omega->isValid()) {
            return redirect()->route('dashboard.import.omega.stepOne', [
                'invalid' => true
            ]);
        }

        $form = Form::create();
        
        $form->add('_confirm', Type\HiddenType::class, [
            'required' => false
        ]);
        
        $form = $form->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $queue = new ParallelJob();

            $queue->add($omega->createImportToDatabaseJob());
            $queue->add(new DeleteFileJob($file));

            $this->dispatch($queue);

            Session::put('om_import_done', true);

            return redirect()->route('dashboard.import.omega.stepThree');
        }

        return view('dashboard/import/omega/2', [
            'confirm_form'  => $form->createView(),
            'current_step'  => 2
        ]);
    }

    /**
     * Grade import wizard step 3
     * 
     * URL: /dashboard/import/omega/finish
     */
    public function stepThree(Request $request) {
        if (!Session::get('om_file')) {
            return redirect()->route('dashboard.import.omega.stepOne');
        }

        if (!Session::get('om_import_done')) {
            return redirect()->route('dashboard.import.omega.stepTwo');
        }

        // cleanup
        Session::forget('om_file');
        Session::forget('om_import_done');
        
        return view('dashboard/import/omega/3', [
            'current_step' => 3
        ]);
    }
}
