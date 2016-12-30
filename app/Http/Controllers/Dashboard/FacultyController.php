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
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use App\Extensions\Constraints as CustomAssert;
use App\Http\Controllers\Controller;
use App\Models\GradeImportLog;
use App\Models\Department;
use App\Models\Faculty;
use App\Extensions\Form;
use App\Extensions\Role;
use App\Extensions\Settings;

class FacultyController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('acl');
    }

    /**
     * Manage faculty accounts page
     * 
     * URL: /dashboard/faculty/
     */
    public function index(Request $request)
    {
        $form = Form::create($request->query->all());
        
        $form->add('name', Type\TextType::class, [
            'required'  => false
        ]);
        
        $result = Faculty::search(
            [['name', 'LIKE', '%' . $request->query->get('name') . '%']],
            ['department']
        );

        return view('dashboard/faculty/index', [
            'search_form'   => $form->getForm()->createView(),
            'result'        => $result
        ]);
    }

    /**
     * My faculty page
     * 
     * URL: /dashboard/faculty/me
     */
    public function me()
    {
        if (!$this->isRole(Role::FACULTY)) {
            abort(404);
        }

        return $this->view($this->user);
    }

    /**
     * Manage faculty summary accounts page
     * 
     * URL: /dashboard/faculty/summary
     */
    public function summary()
    {
        $period = strtolower(Settings::get('period', 'prelim'));

        return view('dashboard/faculty/summary', [
            'faculty'       => Faculty::all(),
            'period'        => $period,
            'periodIndex'   => getPeriodIndex($period)
        ]);
    }

    /**
     * View faculty account page
     * 
     * URL: /dashboard/faculty/{id}
     */
    public function view(Faculty $faculty)
    {
        // Deny if the faculty and head does not belong to the same department
        if ($this->isRole(Role::FACULTY) &&
            (!$faculty->department || $faculty->department->id != $this->user->department->id)) {

            abort(403);
        }

        $period = strtolower(Settings::get('period', 'prelim'));
        $activePeriod = getPeriodIndex($period);

        return view('dashboard/faculty/view', [
            'period'        => $period,
            'faculty'       => $faculty,
            'logs'          => $faculty->submissionLogs->reverse(),

            'statuses'      => [
                $faculty->getStatus('prelim'),
                $faculty->getStatus('midterm'),
                $faculty->getStatus('prefinal'),
                $faculty->getStatus('final')
            ],

            'stats'         => [
                'failed' => [
                    'prelim'    => $faculty->submittedGrades->whereInLoose('prelim_grade', [5])->count(),
                    'midterm'   => $faculty->submittedGrades->whereInLoose('midterm_grade', [5])->count(),
                    'prefinal'  => $faculty->submittedGrades->whereInLoose('prefinal_grade', [5])->count(),
                    'final'     => $faculty->submittedGrades->whereInLoose('final_grade', [5])->count()
                ]
            ]
        ]);
    }

    /**
     * View faculty submission entry
     * 
     * URL: /dashboard/faculty/{id}/submission/{subId}
     */
    public function viewSubmission(Request $request, Faculty $faculty, GradeImportLog $submission)
    {
        if ($faculty->id != $submission->faculty_id) {
            abort(404);
        }

        $grades = $faculty->submittedGrades()->getQuery()
            ->where('subject', $submission->subject)
            ->whereIn('section', explode('/', $submission->section))
            ->with('student')->get();

        $form = Form::create(['status' => $submission->is_valid]);

        $form->add('status', Type\ChoiceType::class, [
            'label'     => 'Set submission status',
            'expanded'  => true,
            'choices'   => [
                'Invalid'   => 0,
                'Valid'     => 1
            ]
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $submission->is_valid = $form['status']->getData();
            $submission->save();

            Session::flash('flash_message', 'success>>>Faculty submission changes has been saved');

            return redirect()->route('dashboard.faculty.view', [
                'faculty' => $faculty->id
            ]);
        }

        return view('dashboard/faculty/view_submission', [
            'faculty'       => $faculty,
            'submission'    => $submission,
            'grades'        => $grades,
            'form'          => $form->createView()
        ]);
    }

    /**
     * Edit faculty account page
     * 
     * URL: /dashboard/faculty/add
     * URL: /dashboard/faculty/{id}/edit
     */
    public function edit(Request $request, Faculty $faculty)
    {
        $mode = $faculty->id ? 'edit' : 'add';
        $form = Form::create($faculty->toArray());

        $departments = Department::getFormChoices();
        $departments['0'] = 'Unassigned';

        $form->add('first_name', Type\TextType::class);
        $form->add('middle_name', Type\TextType::class);
        $form->add('last_name', Type\TextType::class);

        $form->add('department_id', Type\ChoiceType::class, [
            'label'         => 'Department',
            'choices'       => array_flip($departments),
            'data'          => $faculty->department_id ?: '0'
        ]);

        $form->add('username', Type\TextType::class, [
            'constraints' => new CustomAssert\UniqueRecord([
                'model'     => Faculty::class,
                'exclude'   => $faculty->username,
                'row'       => 'username',
                'message'   => 'Username already in use.'
            ])
        ]);

        $form->add('password', Type\RepeatedType::class, [
            'type'      => Type\PasswordType::class,
            'required'  => false,

            'first_options' => ['label' => 'Password (leave blank if not changing)'],
            'second_options' => ['label' => 'Repeat Password (leave blank if not changing)'],

            'constraints' => new Assert\Length([
                'min'        => 8,
                'minMessage' => 'Password should be at least 8 characters long'
            ])
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if ($data['password'] === null) {
                unset($data['password']);
            }

            $faculty->fill($data);
            $faculty->save();

            Session::flash('flash_message', 'success>>>Faculty account has been saved');

            return redirect()->route('dashboard.faculty.index');
        }

        return view('dashboard/faculty/' . $mode, [
            'form'      => $form->createView(),
            'faculty'   => $faculty
        ]);
    }

    /**
     * Delete faculty account page
     * 
     * URL: /dashboard/faculty/{id}/delete
     */
    public function delete(Request $request, Faculty $faculty)
    {
        $form = Form::create();
        $form->add('_confirm', Type\HiddenType::class);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $faculty->delete();

            Session::flash('flash_message', 'info>>>Faculty account has been deleted');

            return redirect()->route('dashboard.faculty.index');
        }

        return view('dashboard/faculty/delete', [
            'form'      => $form->createView(),
            'faculty'   => $faculty
        ]);
    }
}
