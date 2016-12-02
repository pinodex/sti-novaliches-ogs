<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Student;

use Session;
use Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use App\Http\Controllers\Controller;
use App\Extensions\Settings;
use App\Extensions\Form;
use App\Models\Grade;

class MainController extends Controller
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
     * URL: /student/
     */
    public function index()
    {
        if (!$this->user->is_required_info_filled) {
            return redirect()->route('student.account');
        }

        $period = strtolower(Settings::get('period', 'prelim'));

        return view('student/index', [
            'student'       => $this->user,
            'grades'        => $this->user->grades,
            'period'        => $period,
            'active_period' => array_flip(['prelim', 'midterm', 'prefinal', 'final'])[$period],
        ]);
    }

    /**
     * Top students page
     * 
     * URL: /student/top
     */
    public function top(Request $request, $period = null, $subject = null)
    {
        if (!$this->user->is_required_info_filled) {
            return redirect()->route('student.account');
        }
        
        if ($period && !in_array($period, ['prelim', 'midterm', 'prefinal', 'final'])) {
            abort(404);
        }

        $subjects = $this->user->subjects();

        if ($subject && !in_array($subject, $subjects)) {
            abort(404);
        }

        $subjectChoices = [];
        $result = [];

        foreach ($subjects as $choice) {
            $subjectChoices[$choice] = $choice;
        }

        $form = Form::create();

        $form->add('subject', Type\ChoiceType::class, [
            'choices'   => $subjectChoices,
            'data'      => $subject
        ]);

        $form->add('period', Type\ChoiceType::class, [
            'choices' => [
                'Prelim'    => 'prelim',
                'Midterm'   => 'midterm',
                'Pre-final' => 'prefinal',
                'Final'     => 'final'
            ],

            'data'              => $period,
            'expanded'          => true,
            'label'             => ' '
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            return Redirect::route('student.top', $form->getData());
        }

        if ($period && $subject) {
            $result = Grade::getTopByTermAndSubject($period, $subject, $this->user->id);
        }
        
        return view('student/top', [
            'browse_form'   => $form->createView(),
            'period'        => $period,
            'subject'       => $subject,
            'result'        => $result,
        ]);
    }

    /**
     * Dashboard user account settings
     * 
     * Url: /student/account
     */
    public function account(Request $request)
    {
        if ($this->user->email_address === null) {
            $this->user->email_address = toSchoolEmail($this->user->first_name, $this->user->last_name);
        }

        $form = Form::create($this->user->toArray());

        $form->add('mobile_number', Type\TextType::class, [
            'label' => 'Mobile number *',
            'constraints' => new Assert\Regex([
                'pattern'   => '/^((0|63|\+63)([\d]{10}))$/',
                'message'   => 'Please enter a valid mobile number. Eg. 09161234567',
                'match'     => true
            ])
        ]);

        $form->add('landline', Type\TextType::class, [
            'constraints' => new Assert\Regex([
                'pattern'   => '/^(([\d]{9})|([\d]{7}))$/',
                'message'   => 'Please enter a valid landline number. Eg. 8001234',
                'match'     => true
            ]),

            'required' => false
        ]);

        $form->add('email_address', Type\TextType::class, [
            'label'         => 'Email address *',
            'constraints'   => [
                new Assert\Email(),
                new Assert\Regex([
                    'pattern'   => '/\@novaliches\.sti\.edu$/',
                    'message'   => 'You must use your @novaliches.sti.edu email'
                ])
            ]
        ]);

        $form->add('address', Type\TextareaType::class, [
            'label' => 'Address *'
        ]);

        $form->add('guardian_name', Type\TextType::class, [
            'label' => 'Name of guardian/parent *'
        ]);

        $form->add('guardian_contact_number', Type\TextType::class, [
            'label' => 'Guardian&rsquo;s/Parent&rsquo;s contact no. *',
            'constraints' => new Assert\Regex([
                'pattern'   => '/^(([\d]{9})|([\d]{7})|((0|63|\+63)([\d]{10})))$/',
                'message'   => 'Please enter a valid mobile number or landline',
                'match'     => true
            ])
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->user->fill($form->getData());
            $this->user->save();

            Session::flash('flash_message', 'success>>>Your student information has been updated.');

            return redirect()->route('student.index');
        }

        return view('student/account', [
            'settings_form'     => $form->createView(),
            'display_prompt'    => $this->user->is_required_info_filled === false && $request->getMethod() == 'GET'
        ]);
    }
}
