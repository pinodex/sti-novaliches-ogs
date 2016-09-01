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

        Session::forget($sessionId . 'gw_files');
        Session::forget($sessionId . 'gw_import_done');

        Cache::forget($sessionId . 'report');
        Cache::forget($sessionId . 'grading_sheet');

        $form = Form::create();

        $form->add('sgr', Type\FileType::class, [
            'label'         => 'Grading Sheet',
            'attr'          => ['accept' => '.xlsx']
        ]);

        $form->add('omega', Type\FileType::class, [
            'label'         => 'OMEGA File',
            'attr'          => ['accept' => '.xlsx']
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $files = $form->getData();
            
            if ($files['sgr']->getError() > 0 || $files['omega']->getError() > 0) {
                Session::flash('flash_message', 'danger>>>' . $files['sgr']->getErrorMessage());

                return redirect()->route('dashboard.import.grades.stepOne', [
                    'session' => $sessionId
                ]);
            }

            $id = uniqid(null, true);
            
            $sgrTarget = sprintf('/imports/grades/%s-sgr.xlsx', $id);
            $omegaTarget = sprintf('/imports/grades/%s-omega.xlsx', $id);

            Storage::put($sgrTarget, file_get_contents($files['sgr']->getPathname()));
            Storage::put($omegaTarget, file_get_contents($files['omega']->getPathname()));
            
            Session::put($sessionId . 'gw_files', [
                'sgr' => [
                    'name' => $files['sgr']->getClientOriginalName(),
                    'path' => storage_path('app' . $sgrTarget)
                ],

                'omega' => [
                    'name' => $files['omega']->getClientOriginalName(),
                    'path' => storage_path('app' . $omegaTarget)
                ]
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

        if (!$files = Session::get($sessionId . 'gw_files')) {
            return redirect()->route('dashboard.import.grades.stepOne');
        }

        if (Session::get($sessionId . 'gw_import_done')) {
            Session::flash('flash_message', 'info>>>Your SGR was already imported.');

            return redirect()->route('dashboard.import.grades.stepThree', [
                'session' => $sessionId
            ]);
        }

        $sgr = new GradeSpreadsheet($files['sgr']['path']);
        $omega = new OmegaSpreadsheet($files['omega']['path']);

        if (!$sgr->isValid() || !$omega->isValid()) {
            Session::flash('flash_message', 'danger>>>Invalid files uploaded');

            return redirect()->route('dashboard.import.grades.stepOne', [
                'session' => $sessionId
            ]);
        }

        if (!$contents = Cache::get($sessionId . 'grading_sheet')) {
            set_time_limit(0);
            
            $contents = $sgr->getParsedContents();
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
        
            $report = SgrReporter::check($sgr, $omega);

            if ($this->isRole('faculty')) {
                $this->user->addSubmissionLogEntry($report);
                $importer = $this->user;
            }

            $sgr->importToDatabase($importer, $report->getMismatches()->pluck('student_id'));

            Cache::put($sessionId . 'report', $report, 60);

            $queue = new ParallelJob();

            try {
                $email = new GradeDelivery();
                $email->attach($files['sgr']['path'], $files['sgr']['name'], mime_content_type($files['sgr']['path']));

                $queue->add(new SendEmailJob($email));
            } catch (\Exception $ignored) {}

            $queue->add(new DeleteFileJob($files['sgr']['path']));
            $queue->add(new DeleteFileJob($files['omega']['path']));

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

        if (!$files = Session::get($sessionId . 'gw_files')) {
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

        if (!Session::get($sessionId . 'gw_files')) {
            return redirect()->route('dashboard.import.grades.stepOne');
        }

        if (!Session::get($sessionId . 'gw_import_done')) {
            return redirect()->route('dashboard.import.grades.stepTwo');
        }

        // cleanup
        Session::forget($sessionId . 'gw_files');
        Session::forget($sessionId . 'gw_import_done');

        Cache::forget($sessionId . 'report');
        Cache::forget($sessionId . 'grading_sheet');
        
        return view('dashboard/import/grades/4', [
            'session_id'    => $sessionId,
            'current_step' => 4
        ]);
    }
}
