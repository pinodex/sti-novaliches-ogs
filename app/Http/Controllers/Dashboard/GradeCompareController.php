<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Dashboard;

use Cache;
use Session;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use App\Http\Controllers\Controller;
use App\Extensions\Constraints as CustomAssert;
use App\Extensions\Parser\MasterGradingSheet;
use App\Extensions\GradesComparator;
use App\Extensions\Form;
use App\Models\Grade;

class GradeCompareController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('acl');
    }

    /**
     * Grade compare upload page
     * 
     * URL: /dashboard/grades/compare/upload
     */
    public function upload(Request $request)
    {
        Cache::forget('master_grading_sheet');

        $form = Form::create();

        $form->add('file', Type\FileType::class, [
            'label' => ' ',
            'attr'  => ['accept' => 'text/csv']
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $file = $form['file']->getData();

            if ($file->getError() != 0) {
                Session::flash('flash_message', 'danger>>>' . $file->getErrorMessage());
                
                return redirect()->route('dashboard.grades.compare.upload');
            }

            $gradingSheet = MasterGradingSheet::parse($file->getPathname());
            
            Cache::put('master_grading_sheet', $gradingSheet->getSheetContents(0), 60);

            return redirect()->route('dashboard.grades.compare.diff');
        }

        return view('dashboard/grades/compare/upload', [
            'upload_form'   => $form->createView(),
        ]);
    }

    /**
     * Grade compare diff page
     * 
     * URL: /dashboard/grades/compare/diff
     */
    public function diff(Request $request)
    {
        if (!$masterGradingSheet = Cache::get('master_grading_sheet')) {
            return redirect()->route('dashboard.grades.compare.upload');
        }

        $query = Grade::with('student', 'importer');

        if (!$aggregation = Cache::get('master_grading_sheet_aggregation')) {
            $comparator = new GradesComparator($masterGradingSheet, $query);
            $aggregation = $comparator->getMismatches();

            Cache::put('master_grading_sheet_aggregation', $aggregation, 60);
        }

        $page = $request->query->get('page', 1);

        return view('dashboard/grades/compare/diff', [
            'result' => new LengthAwarePaginator(
                $aggregation->forPage($page, 50),
                $aggregation->count(), 50
            )
        ]);
    }
}
