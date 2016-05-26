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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
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
        $comparator = new GradesComparator($this->cache->get('master_grading_sheet'), $query);
        $aggregation = $comparator->getMismatches();

        // TODO: student name and importer name
        // PLUCK PLUCK PLUCK
        
        /*
        $aggregation = new Collection();
        $csvData = Collection::make();
        
        // Chunk to multiple queries get pass the database's limits
        $csvData->chunk(250)->each(function (Collection $chunk) use (&$aggregation) {
            $query = Grade::with('student', 'importer');

            $chunk->each(function ($record) use ($query) {
                $query->where(function (Builder $builder) use ($record) {
                    foreach ($record as $key => $value) {
                        if ($value === null) {
                            $builder->orWhereNotNull($key);
                        } else {
                            $builder->orWhere($key, '!=', $value);
                        }
                    }
                });
            });

            $mismatches = $query->get(array(
                'student_id', 'importer_id', 'subject', 'section', 'prelim_grade', 'midterm_grade', 'prefinal_grade', 'final_grade'
            ));

            $mismatches->each(function (Grade $entry) use ($chunk, &$aggregation) {
                $mismatch = array(
                    'target' => $entry->toArray(),
                    'source' => null
                );

                $searchId = $chunk->search(function ($item) use ($mismatch) {
                    return $mismatch['target']['student_id'] == $item['student_id'] &&
                           $mismatch['target']['section']    == $item['section'] &&
                           $mismatch['target']['subject']    == $item['subject'];
                });

                if ($searchId !== false) {
                    $mismatch['source'] = $chunk->get($searchId);
                }

                // Check if mismatched item is already aggregated
                $aggregationSearch = $aggregation->search(function ($item) use ($mismatch) {
                    return $mismatch['target']['student_id'] == $item['target']['student_id'] &&
                           $mismatch['target']['section']    == $item['target']['section'] &&
                           $mismatch['target']['subject']    == $item['target']['subject'];
                });

                // Add new mismatch entry if not yet aggregated
                if ($aggregationSearch === false) {
                    $aggregation->push($mismatch);
                }
            });
        });
        */

        return View::render('dashboard/grades/compare/diff', array(
            'result' => (new LengthAwarePaginator(
                $aggregation->forPage($page, 50),
                $aggregation->count(), 50
            ))->toArray()
        ));
    }
}
