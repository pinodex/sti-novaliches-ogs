<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers\Dashboard;

use Silex\Application;
use App\Models\Grade;
use App\Services\View;
use App\Services\Form;
use App\Services\FlashBag;
use App\Controllers\Controller;
use App\Components\GradesComparator;
use App\Components\Parser\MasterGradingSheet;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

/**
 * Route controller for grade pages
 */
class GradesCompareController extends Controller
{
    /**
     * Grades page index
     * 
     * URL: /dashboard/grades/
     */
    public function index(Application $app)
    {
        return $app->redirect($app->path('dashboard.grades.compare.upload'));
    }

    public function upload(Request $request, Application $app)
    {
        $this->cache->remove('master_grading_sheet');

        $form = Form::create();

        $form->add('file', 'file', array(
            'label' => ' ',
            'attr'  => array(
                'accept' => 'text/csv'
            )
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $file = $form['file']->getData();

            if ($file->getError() != 0) {
                FlashBag::add('messages', 'danger>>>' . $file->getErrorMessage());
                
                return $app->redirect($app->path('dashboard.grades.compare.upload'));
            }

            $gradingSheet = MasterGradingSheet::parse($file->getPathname());
            $this->cache->put('master_grading_sheet', $gradingSheet->getSheetContents(0));

            return $app->redirect($app->path('dashboard.grades.compare.diff'));
        }

        return View::render('dashboard/grades/compare/upload', array(
            'upload_form'   => $form->createView(),
        ));
    }

    public function diff(Request $request, Application $app)
    {
        if (!$this->cache->has('master_grading_sheet')) {
            return $app->redirect($app->path('dashboard.grades.compare.upload'));
        }

        if ($page = $request->query->get('page')) {
            Paginator::currentPageResolver(function () use ($page) {
                return $page;
            });
        }

        $query = Grade::with('student', 'importer');

        if (!$aggregation = $this->cache->get('master_grading_sheet_aggregation')) {
            $comparator = new GradesComparator($this->cache->get('master_grading_sheet'), $query);
            $aggregation = $comparator->getMismatches();

            $this->cache->put('master_grading_sheet_aggregation', $aggregation);
        }

        return View::render('dashboard/grades/compare/diff', array(
            'result' => (new LengthAwarePaginator(
                $aggregation->forPage($page, 50),
                $aggregation->count(), 50
            ))->toArray()
        ));
    }
}
