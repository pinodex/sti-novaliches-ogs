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

use Session;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use App\Extensions\Constraints as CustomAssert;
use App\Http\Controllers\Controller;
use App\Extensions\Form;
use App\Models\Faculty;
use App\Models\Grade;

class SectionController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('acl');
    }

    /**
     * Sections index page
     * 
     * URL: /dashboard/sections/
     */
    public function index(Request $request)
    {
        $form = Form::create($request->query->all());
        
        $form->add('section', Type\TextType::class, [
            'required'  => false
        ]);

        $form->add('subject', Type\TextType::class, [
            'label'     => 'Subject code',
            'required'  => false
        ]);

        $form->add('faculty', Type\ChoiceType::class, [
            'choices'   => array_flip(Faculty::getFormChoices()),
            'required'  => false
        ]);

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
        
        $aggregatedResults = [];
        $periods = ['prelim', 'midterm', 'prefinal', 'final'];

        $all = $grades->get()->groupBy(function (Grade $grade) {
            return $grade->section . '/' . $grade->subject;
        });

        foreach ($all as $sectionSubject => $grades) {
            $entry = [
                'section'   => explode('/', $sectionSubject)[0],
                'subject'   => explode('/', $sectionSubject)[1],
                'count'     => count($grades),
                'dropped'   => [
                    'prelim'    => 0,
                    'midterm'   => 0,
                    'prefinal'  => 0,
                    'final'     => 0
                ],

                'failed'    => [
                    'prelim'    => 0,
                    'midterm'   => 0,
                    'prefinal'  => 0,
                    'final'     => 0
                ],

                'nograde'   => [
                    'prelim'    => 0,
                    'midterm'   => 0,
                    'prefinal'  => 0,
                    'final'     => 0
                ]
            ];

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

        $page = $request->query->get('page', 1);

        $result = new LengthAwarePaginator(
            array_slice($aggregatedResults, (50 * ($page - 1)), 50), count($aggregatedResults), 50
        );

        return view('dashboard/sections/index', [
            'search_form'   => $form->getForm()->createView(),
            'result'        => $result
        ]);
    }
}
