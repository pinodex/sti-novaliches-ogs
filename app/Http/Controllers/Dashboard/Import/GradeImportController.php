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

        Session::forget($sessionId . 'gw_sgr');
        Session::forget($sessionId . 'gw_import_done');

        Cache::forget($sessionId . 'report');
        Cache::forget($sessionId . 'grading_sheet');

        $form = Form::create();

        $form->add('sgr', Type\FileType::class, [
            'label'         => 'SGR File',
            'attr'          => ['accept' => '.xlsx']
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form->getData();
            
            if ($data['sgr']->getError() > 0) {
                Session::flash('flash_message', 'danger>>>' . $data['sgr']->getErrorMessage());

                return redirect()->route('dashboard.import.grades.stepOne', [
                    'session' => $sessionId
                ]);
            }

            $id = uniqid(null, true);
            
            $sgrTarget = sprintf('/imports/grades/%s-sgr.xlsx', $id);

            Storage::put($sgrTarget, file_get_contents($data['sgr']->getPathname()));
            
            Session::put($sessionId . 'gw_sgr', [
                'name' => $data['sgr']->getClientOriginalName(),
                'path' => storage_path('app' . $sgrTarget)
            ]);
            
            return redirect()->route('dashboard.import.grades.stepTwo', [
                'session' => $sessionId
            ]);
        }
        
        return view('dashboard/import/grades/1', [
            'session_id'    => $sessionId,
            'upload_form'   => $form->createView(),
            'current_step'  => 1,
            'invalid'       => $request->query->get('invalid')
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

        if (!$file = Session::get($sessionId . 'gw_sgr')) {
            return redirect()->route('dashboard.import.grades.stepOne');
        }

        if (Session::get($sessionId . 'gw_import_done')) {
            Session::flash('flash_message', 'info>>>Your SGR was already imported.');

            return redirect()->route('dashboard.import.grades.stepThree', [
                'session' => $sessionId
            ]);
        }

        $sgr = new GradeSpreadsheet($file['path']);

        if (!$sgr->isValid()) {
            return redirect()->route('dashboard.import.grades.stepOne', [
                'session' => $sessionId,
                'invalid' => true
            ]);
        }

        if (!$contents = Cache::get($sessionId . 'grading_sheet')) {
            set_time_limit(0);
            
            try {
                $contents = $sgr->getParsedContents();
            } catch (\Exception $e) {
                return redirect()->route('dashboard.import.grades.stepOne', [
                    'session' => $sessionId,
                    'invalid' => true
                ]);
            }

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
        
            $report = SgrReporter::check($sgr);

            if ($this->isRole('faculty')) {
                $this->user->addSubmissionLogEntry($report);
                $importer = $this->user;
            }

            $sgr->importToDatabase($importer, $report->getMismatches()->pluck('student_id'));

            Cache::put($sessionId . 'report', $report, 60);

            $queue = new ParallelJob();

            try {
                $email = new GradeDelivery();
                $email->attach($file['path'], $file['name'], mime_content_type($file['path']));

                $queue->add(new SendEmailJob($email));
            } catch (\Exception $ignored) {}

            $queue->add(new DeleteFileJob($file['path']));

            $this->dispatch($queue);

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

        if (!$file = Session::get($sessionId . 'gw_sgr')) {
            return redirect()->route('dashboard.import.grades.stepOne');
        }

        if (!Session::get($sessionId . 'gw_import_done')) {
            return redirect()->route('dashboard.import.grades.stepTwo');
        }

        $report = Cache::get($sessionId . 'report');

        return view('dashboard/import/grades/3', [
            'report'        => $report,
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

        if (!Session::get($sessionId . 'gw_sgr')) {
            return redirect()->route('dashboard.import.grades.stepOne');
        }

        if (!Session::get($sessionId . 'gw_import_done')) {
            return redirect()->route('dashboard.import.grades.stepTwo');
        }

        // cleanup
        Session::forget($sessionId . 'gw_sgr');
        Session::forget($sessionId . 'gw_import_done');

        Cache::forget($sessionId . 'report');
        Cache::forget($sessionId . 'grading_sheet');
        
        return view('dashboard/import/grades/4', [
            'session_id'    => $sessionId,
            'current_step' => 4
        ]);
    }
}
