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

        $vars = array();

        $form = Form::create(null, array(
            'csrf_protection' => false
        ));
        
        $form->add('section', 'text', array(
            'required'  => false,
            'data'      => $request->query->get('section')
        ));

        $form->add('faculty', 'choice', array(
            'required'  => false,
            'data'      => $request->query->get('faculty'),
            'choices'   => Faculty::getFormChoices()
        ));

        $vars['search_form'] = $form->getForm()->createView();

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

        $vars['result'] = (new LengthAwarePaginator(
            array_slice($sections, (50 * ($page - 1)), 50), count($sections), 50
        ))->toArray();

        $vars['students'] = Grade::whereIn('student_id', $studentIds)->get()->keyBy('student_id');

        return View::render('dashboard/sections/index', $vars);
    }

    /**
     * Sections index page
     * 
     * URL: /dashboard/sections/{section}
     */
    public function view(Request $request, Application $app, $section)
    {
        $gradeQuery = Grade::where('section', $section);

        if ($gradeQuery->count() == 0) {
            $app->abort(404);
        }

        $vars = array(
            'section' => $section
        );

        $studentIds = array();

        $form = Form::create(null, array(
            'csrf_protection' => false
        ));
        
        $form->add('id', 'text', array(
            'label'     => 'Search by number',
            'required'  => false,
            'data'      => $request->query->get('id')
        ));
        
        $form->add('name', 'text', array(
            'label'     => 'Search by name',
            'required'  => false,
            'data'      => $request->query->get('name')
        ));

        $gradeQuery->get()->each(function (Grade $grade) use (&$studentIds) {
            $studentIds[] = $grade->student_id;
        });

        $query = array(
            array('id', $studentIds)
        );

        if ($id = $request->query->get('id')) {
            $query[] = array('id', 'LIKE', $id);
        }

        if ($name = $request->query->get('name')) {
            $query[] = array('name', 'LIKE', '%' . $name . '%');
        }

        $vars['search_form'] = $form->getForm()->createView();
        $vars['result'] = Student::search($query)->toArray();

        return View::render('dashboard/sections/view', $vars);
    }
}
