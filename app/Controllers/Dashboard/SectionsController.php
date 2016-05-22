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

        $form->add('faculty', 'choice', array(
            'required'  => false,
            'choices'   => Faculty::getFormChoices()
        ));

        $context['search_form'] = $form->getForm()->createView();

        $grades = Grade::orderBy('section', 'ASC');

        if ($section = $request->query->get('section')) {
            $grades->where('section', 'LIKE', '%' . $section . '%');
        }

        if ($faculty = $request->query->get('faculty')) {
            $grades->where('importer_id', $faculty);
        }

        $all = $grades->get()->groupBy('section');
        $studentIds = array();

        $sections = array_combine(
            $all->keys()->toArray(),

            $all->map(function ($section) {
                return array_unique(Arr::pluck($section, 'student_id'));
            })->toArray()
        );

        array_walk_recursive($sections, function ($studentId) use (&$studentIds) {
            $studentIds[] = $studentId;
        });

        $context['result'] = (new LengthAwarePaginator(
            array_slice($sections, (50 * ($page - 1)), 50), count($sections), 50
        ))->toArray();

        return View::render('dashboard/sections/index', $context);
    }

    /**
     * Section summary page
     * 
     * URL: /dashboard/sections/{section}
     */
    public function summary(Request $request, Application $app, $section)
    {
        $query = Grade::where('section', $section);

        if ($query->count() == 0) {
            $app->abort(404);
        }

        $periods = array('prelim', 'midterm', 'prefinal', 'final');
        $subjects = array();

        $query->groupBy('subject')->get()->each(function (Grade $grade) use (&$subjects, $periods) {
            if (!array_key_exists($grade->subject, $subjects)) {
                // Initialize section entry with zero values
                $subjects[$grade->subject] = array(
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
                    )
                );
            }

            foreach ($periods as $period) {
                $value = $grade->getOriginal($period . '_grade');

                if ($value < 75) {
                    $subjects[$grade->subject]['failed'][$period]++;
                }

                if ($value == -1) {
                    $subjects[$grade->subject]['dropped'][$period]++;
                }
            }
        });

        return View::render('dashboard/sections/summary', array(
            'subjects'  => $subjects,
            'section'   => $section
        ));
    }
}
