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
use App\Services\Session;
use App\Controllers\Controller;
use App\Components\Parser\MasterGradingSheet;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;

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
        $fs = new Filesystem();

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
        
        $aggregation = array();
        $csvData = new Collection($this->cache->get('master_grading_sheet'));
        
        // Chunk to multiple queries get pass the database's limits
        $csvData->chunk(250)->each(function (Collection $chunk) use (&$aggregation) {
            $query = Grade::with('student', 'importer');

            $chunk->each(function ($record) use ($query) {
                $query->where(function (Builder $builder) use ($record) {
                    foreach ($record as $key => $value) {
                        $builder->orWhere($key, '!=', $value);
                    }
                });
            });

            $mismatches = $query->get(array(
                'student_id', 'importer_id', 'subject', 'section', 'prelim_grade', 'midterm_grade', 'prefinal_grade', 'final_grade'
            ));

            $mismatches->each(function ($entry) use ($chunk, &$aggregation) {
                $target = $entry->toArray();
                $source = null;

                $searchId = $chunk->search(function ($item) use ($target) {
                    return $item['student_id']  == $target['student_id'] &&
                           $item['section']     == $target['section'] &&
                           $item['subject']     == $target['subject'];
                });

                if ($searchId !== false) {
                    $source = $chunk->get($searchId);
                }

                $aggregation[] = array(
                    'source' => $source,
                    'target' => $target
                );
            });
        });

        return View::render('dashboard/grades/compare/diff', array(
            'result' => (new LengthAwarePaginator(
                array_slice($aggregation, (50 * ($page - 1)), 50), count($aggregation), 50
            ))->toArray()
        ));
    }
}
