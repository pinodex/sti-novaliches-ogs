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
use App\Models\Faculty;
use App\Models\Student;
use App\Services\View;
use App\Services\Form;
use App\Controllers\Controller;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;

/**
 * Route controller for section management pages
 */
class SectionsController extends Controller
{
    /**
     * Sections index page
     * 
     * URL: /dashboard/sections/
     */
    public function index(Request $request)
    {
        if ($page = $request->query->get('page')) {
            Paginator::currentPageResolver(function () use ($page) {
                return $page;
            });
        }

        $context = array();

        $form = Form::create($request->query->all(), array(
            'csrf_protection' => false
        ));
        
        $form->add('section', 'text', array(
            'required'  => false
        ));

        $form->add('subject', 'text', array(
            'label'     => 'Subject code',
            'required'  => false
        ));

        $form->add('faculty', 'choice', array(
            'required'  => false,
            'choices'   => Faculty::getFormChoices()
        ));

        $context['search_form'] = $form->getForm()->createView();

        $grades = Grade::orderBy('section', 'ASC')->orderBy('subject', 'ASC');

        if ($section = $request->query->get('section')) {
            $grades->where('section', 'LIKE', '%' . $section . '%');
        }

        if ($subject = $request->query->get('subject')) {
            $grades->where('subject', 'LIKE', '%' . $subject . '%');
        }

        if ($faculty = $request->query->get('faculty')) {
            $grades->where('importer_id', $faculty);
        }
        
        $aggregatedResults = array();
        $periods = array('prelim', 'midterm', 'prefinal', 'final');

        $all = $grades->get()->groupBy(function (Grade $grade) {
            return $grade->section . '/' . $grade->subject;
        });

        foreach ($all as $sectionSubject => $grades) {
            $entry = array(
                'section'   => explode('/', $sectionSubject)[0],
                'subject'   => explode('/', $sectionSubject)[1],
                'count'     => count($grades),
                'dropped'   => array(
                    'prelim'    => 0,
                    'midterm'   => 0,
                    'prefinal'  => 0,
                    'final'     => 0
                ),

                'failed'    => array(
                    'prelim'    => 0,
                    'midterm'   => 0,
                    'prefinal'  => 0,
                    'final'     => 0
                ),

                'nograde'   => array(
                    'prelim'    => 0,
                    'midterm'   => 0,
                    'prefinal'  => 0,
                    'final'     => 0
                )
            );

            foreach ($grades as $grade) {
                foreach ($periods as $period) {
                    $value = $grade->getOriginal($period . '_grade');

                    if ($value === null) {
                        $entry['nograde'][$period]++;
                    }

                    if ($value !== null && $value < 75) {
                        $entry['failed'][$period]++;
                    }

                    if ($value !== null && $value == -1) {
                        $entry['dropped'][$period]++;
                    }
                }
            }

            $aggregatedResults[] = $entry;
        }

        $context['result'] = (new LengthAwarePaginator(
            array_slice($aggregatedResults, (50 * ($page - 1)), 50), count($aggregatedResults), 50
        ))->toArray();

        return View::render('dashboard/sections/index', $context);
    }
}
