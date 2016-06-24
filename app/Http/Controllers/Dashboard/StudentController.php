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
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use App\Extensions\Constraints as CustomAssert;
use App\Http\Controllers\Controller;
use App\Extensions\Settings;
use App\Extensions\Form;
use App\Models\Student;
use App\Models\Grade;

class StudentController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('acl');
    }

    /**
     * Student page index
     * 
     * URL: /dashboard/students/
     */
    public function index(Request $request)
    {
        $form = Form::create($request->query->all());
        
        $form->add('id', Type\TextType::class, [
            'label'     => 'Search by number',
            'required'  => false
        ]);
        
        $form->add('name', Type\TextType::class, [
            'label'     => 'Search by name',
            'required'  => false
        ]);

        $form->add('section', Type\TextType::class, [
            'label'     => 'Search by section',
            'required'  => false
        ]);
        
        $query = [];
        $builderHook = null;

        $request->query->set('id', parseStudentId($request->query->get('id')));

        if ($id = $request->query->get('id')) {
            $query[] = ['id', 'LIKE', $id];
        }

        if ($name = $request->query->get('name')) {
            $query[] = ['name', 'LIKE', '%' . $name . '%'];
        }

        $section = $request->query->get('section');

        $builderHook = function (Builder $builder) use ($section) {
            if ($this->isRole('faculty') || $section) {
                $builder->leftJoin('grades', 'students.id', '=', 'grades.student_id');
            }

            if ($this->isRole('faculty')) {
                $builder->where('importer_id', $this->user->getModel()->id);
            }

            if ($section) {
                $builder->where('section', $section);
            }

            $builder->groupBy('id');
        };

        $result = Student::search($query, null, $builderHook);

        return view('dashboard/students/index', [
            'search_form'   => $form->getForm()->createView(),
            'section'       => $section,
            'result'        => $result
        ]);
    }

    /**
     * View student page
     * 
     * URL: /dashboard/students/{id}
     */
    public function view(Student $student)
    {
        if ($this->isRole('faculty')) {
            if ($student->grades()->getQuery()->where('importer_id', $this->user->id)->count() == 0) {
                abort(403);
            }
        }

        $period = strtolower(Settings::get('period', 'prelim'));

        return view('dashboard/students/view', [
            'student'       => $student,
            'grades'        => $student->grades,
            'period'        => $period,
            'active_period' => array_flip(['prelim', 'midterm', 'prefinal', 'final'])[$period]
        ]);
    }

    /**
     * Add/Edit student info
     * 
     * URL: /dashboard/students/add
     * URL: /dashboard/students/{id}/edit
     */
    public function edit(Request $request, Student $student)
    {
        $mode = $student->id ? 'edit' : 'add';
        $form = Form::create($student->toArray());

        $form->add('id', Type\TextType::class, [
            'label' => 'Student ID',

            'constraints' => [
                new Assert\Regex([
                    'pattern'   => '/([\d+]{3}-[\d+]{4}-[\d+]{4})|([\d+]{3})([\d+]{4})([\d+]{4})/',
                    'match'     => true,
                    'message'   => 'Invalid Student ID format'
                ]),

                new CustomAssert\UniqueRecord([
                    'model'     => Student::class,
                    'exclude'   => $student->id,
                    'row'       => 'id',
                    'message'   => 'Student ID already in use.'
                ])
            ]
        ]);

        $form->add('first_name', Type\TextType::class);
        $form->add('middle_name', Type\TextType::class);
        $form->add('last_name', Type\TextType::class);
        $form->add('course', Type\TextType::class);

        $form->add('mobile_number', Type\TextType::class, [
            'constraints' => new Assert\Regex([
                'pattern'   => '/(0|63|\+63)[\d+]{10}/',
                'message'   => 'Please enter a valid mobile number',
                'match'     => true
            ]),

            'required' => false
        ]);

        $form->add('landline', Type\TextType::class, [
            'constraints' => new Assert\Regex([
                'pattern'   => '/([\d+]{3}[\d+]{4})|([\d+]{3}-[\d+]{4})/',
                'message'   => 'Please enter a valid landline number',
                'match'     => true
            ]),

            'required' => false
        ]);

        $form->add('email_address', Type\TextType::class, [
            'constraints' => new Assert\Email(),
            'required' => false
        ]);

        $form->add('address', Type\TextareaType::class, [
            'label' => 'Address',
            'required' => false
        ]);

        $form->add('guardian_name', Type\TextType::class, [
            'label' => 'Name of guardian/parent',
            'required' => false
        ]);

        $form->add('guardian_contact_number', Type\TextType::class, [
            'label' => 'Guardian\'s/Parent\'s contact no.',
            'constraints' => new Assert\Regex([
                'pattern'   => '/([\d+]{3}[\d+]{4})|([\d+]{3}-[\d+]{4})|((0|63|\+63)[\d+]{10})/',
                'message'   => 'Please enter a valid mobile number or landline',
                'match'     => true
            ]),

            'required' => false
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $student->fill($form->getData());
            $student->save();

            Session::flash('flash_message', 'success>>>Student has been saved');

            return redirect()->route('dashboard.students.view', [
                'id' => $student->id
            ]);
        }

        return view('dashboard/students/' . $mode, [
            'form'      => $form->createView(),
            'student'   => $student
        ]);
    }

    /**
     * Edit student page
     * 
     * URL: /dashboard/students/{id}/grades/edit
     */
    public function gradesEdit(Request $request, Student $student)
    {
        $grades = $student->grades->toArray();

        if (empty($grades)) {
            abort(404);
        }

        $subjectSet = [];

        foreach ($grades as $grade) {
            $subjectSet[] = $grade['subject'];
        }

        if ($request->getMethod() == 'POST') {
            $gradesInput = $request->request->get('grades');

            // Check if the input subjects matches the current subject set
            foreach ($gradesInput as $i => $inputItem) {
                if (!in_array($inputItem['subject'], $subjectSet)) {
                    unset($gradesInput[$i]);
                }
            }

            $student->updateGrades($gradesInput);

            return redirect()->route('dashboard.students.view', [
                'id' => $student->id
            ]);
        }

        return view('dashboard/students/grades_edit', [
            'student'   => $student,
            'grades'    => $grades
        ]);
    }

    /**
     * Delete student
     * 
     * URL: /dashboard/students/{id}/delete
     */
    public function delete(Request $request, Student $student)
    {
        $form = Form::create();
        $form->add('_confirm', Type\HiddenType::class);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $student->delete();

            Session::flash('flash_message', 'info>>>Student has been deleted');

            return redirect()->route('dashboard.students.index');
        }

        return view('dashboard/students/delete', [
            'form'      => $form->createView(),
            'student'   => $student
        ]);
    }
}
