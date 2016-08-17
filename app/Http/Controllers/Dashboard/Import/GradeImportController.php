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
use App\Extensions\Spreadsheet\GradeSpreadsheet;
use App\Http\Controllers\Controller;
use App\Jobs\ParallelJob;
use App\Jobs\DeleteFileJob;
use App\Jobs\SendEmailJob;
use App\Extensions\Form;
use App\Extensions\SgrReporter;
use App\Extensions\Email\GradeDelivery;

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
        return redirect()->route('dashboard.import.grades.stepOne', [
            'session' => sha1(uniqid(null, true))
        ]);
    }

    /**
     * Grade import wizard step 1
     * 
     * URL: /dashboard/import/grades/upload
     */
    public function stepOne(Request $request) {
        if (!$sessionId = $request->query->get('session')) {
            return redirect()->route('dashboard.import.grades');
        }

        if ($uploadedFile = Session::get('gw_uploaded_file')) {
            @unlink($uploadedFile);
        }

        Session::forget($sessionId . 'gw_file');
        Session::forget($sessionId . 'gw_import_done');

        Cache::forget($sessionId . 'grading_sheet');

        $form = Form::create();

        $form->add('file', Type\FileType::class, [
            'label' => ' '
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $file = $form['file']->getData();
            
            if ($file->getError() > 0) {
                Session::flash('flash_message', 'danger>>>' . $file->getErrorMessage());

                return redirect()->route('dashboard.import.grades.stepOne', [
                    'session' => $sessionId
                ]);
            }

            $storageName = sprintf('/imports/grades/%s.xlsx', uniqid(null, true));

            Storage::put($storageName, file_get_contents($file->getPathname()));
            
            Session::put($sessionId . 'gw_file', [
                'name' => $file->getClientOriginalName(),
                'path' => storage_path('app' . $storageName)
            ]);
            
            return redirect()->route('dashboard.import.grades.stepTwo', [
                'session' => $sessionId
            ]);
        }
        
        return view('dashboard/import/grades/1', [
            'session_id'    => $sessionId,
            'upload_form'   => $form->createView(),
            'current_step'  => 1
        ]);
    }

    /**
     * Grade import wizard step 2
     * 
     * URL: /dashboard/import/grades/confirm
     */
    public function stepTwo(Request $request) {
        if (!$sessionId = $request->query->get('session')) {
            return redirect()->route('dashboard.import.grades');
        }

        if (!$file = Session::get($sessionId . 'gw_file')) {
            return redirect()->route('dashboard.import.grades.stepOne');
        }

        $spreadsheet = new GradeSpreadsheet($file['path']);

        if (!$spreadsheet->isValid()) {
            Session::flash('flash_message', 'danger>>>' . 'Please upload a valid grading sheet file.');
            return redirect()->route('dashboard.import.grades.stepOne');
        }

        if (!$contents = Cache::get($sessionId . 'grading_sheet')) {
            set_time_limit(0);
            
            $contents = $spreadsheet->getParsedContents();
            Cache::put($sessionId . 'grading_sheet', $contents, 60);
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
                $importer = $this->user;
            }

            $spreadsheet->importToDatabase($importer);

            Session::put($sessionId . 'gw_import_done', true);

            return redirect()->route('dashboard.import.grades.stepThree', [
                'session' => $sessionId
            ]);
        }

        return view('dashboard/import/grades/2', [
            'session_id'    => $sessionId,
            'confirm_form'  => $form->createView(),
            'contents'      => $contents,
            'current_step'  => 2
        ]);
    }

    /**
     * Grade import wizard step 3
     * 
     * URL: /dashboard/import/grades/report
     */
    public function stepThree(Request $request) {
        if (!$sessionId = $request->query->get('session')) {
            return redirect()->route('dashboard.import.grades');
        }

        if (!$file = Session::get($sessionId . 'gw_file')) {
            return redirect()->route('dashboard.import.grades.stepOne');
        }

        if (!Session::get($sessionId . 'gw_import_done')) {
            return redirect()->route('dashboard.import.grades.stepTwo');
        }

        $spreadsheet = new GradeSpreadsheet($file['path']);
        $report = SgrReporter::check($spreadsheet);

        $form = Form::create();
        
        $form->add('_confirm', Type\HiddenType::class, [
            'required' => false
        ]);
        
        $form = $form->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            if ($this->isRole('faculty')) {
                $this->user->addSubmissionLogEntry($report->isValid());
            }

            $queue = new ParallelJob();

            try {
                $email = new GradeDelivery();
                $email->attach($file['path'], $file['name'], mime_content_type($file['path']));

                $queue->add(new SendEmailJob($email));
            } catch (\Exception $ignored) {}

            $queue->add(new DeleteFileJob($file['path']));
            $this->dispatch($queue);

            return redirect()->route('dashboard.import.grades.stepFour', [
                'session' => $sessionId
            ]);
        }

        return view('dashboard/import/grades/3', [
            'report'        => $report,
            'uploaded'      => $report->getTotalImports() - count($report->getNoStudents()),
            'confirm_form'  => $form->createView(),
            'session_id'    => $sessionId,
            'current_step'  => 3
        ]);
    }

    /**
     * Grade import wizard step 4
     * 
     * URL: /dashboard/import/grades/finish
     */
    public function stepFour(Request $request) {
        if (!$sessionId = $request->query->get('session')) {
            return redirect()->route('dashboard.import.grades');
        }

        if (!Session::get($sessionId . 'gw_file')) {
            return redirect()->route('dashboard.import.grades.stepOne');
        }

        if (!Session::get($sessionId . 'gw_import_done')) {
            return redirect()->route('dashboard.import.grades.stepTwo');
        }

        // cleanup
        Session::forget($sessionId . 'gw_file');
        Session::forget($sessionId . 'gw_import_done');

        Cache::forget($sessionId . 'grading_sheet');
        
        return view('dashboard/import/grades/4', [
            'session_id'    => $sessionId,
            'current_step' => 4
        ]);
    }
}
