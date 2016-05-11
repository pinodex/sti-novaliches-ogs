<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers\Student;

use Silex\Application;
use App\Services\Auth;
use App\Services\Form;
use App\Services\View;
use App\Services\Settings;
use App\Services\Session\FlashBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use App\Models\Grade;

/**
 * Student controller
 * 
 * Route controllers for /student/*
 */
class MainController
{
    /**
     * Student page index
     * 
     * URL: /student/
     */
    public function index(Application $app)
    {
        $user = Auth::user()->getModel();

        if (!$user->is_required_info_filled) {
            return $app->redirect($app->path('student.account'));
        }

        $period = strtolower(Settings::get('period', 'prelim'));
        $periodIndex = array_flip(array('prelim', 'midterm', 'prefinal', 'final'))[$period];

        return View::render('student/index', array(
            'student'       => $user->toArray(),
            'grades'        => $user->grades->toArray(),
            'period'        => $period,
            'active_period' => $periodIndex
        ));
    }

    /**
     * Top students page
     * 
     * URL: /student/top
     */
    public function top(Request $request, Application $app, $period, $subject)
    {
        if ($period && !in_array($period, array('prelim', 'midterm', 'prefinal', 'final'))) {
            return $app->abort(404);
        }

        $user = Auth::user()->getModel();

        if (!$user->is_required_info_filled) {
            return $app->redirect($app->path('student.account'));
        }

        $subjects = $user->subjects();

        if ($subject && !in_array($subject, $subjects)) {
            return $app->abort(404);
        }

        $subjectChoices = array();
        $result = array();

        foreach ($subjects as $choice) {
            $subjectChoices[$choice] = $choice;
        }

        $form = Form::create();

        $form->add('subject', 'choice', array(
            'choices'   => $subjectChoices,
            'data'      => $subject
        ));

        $form->add('period', 'choice', array(
            'choices' => array(
                'Prelim'    => 'prelim',
                'Midterm'   => 'midterm',
                'Pre-final' => 'prefinal',
                'Final'     => 'final'
            ),

            'choices_as_values' => true,
            'data'              => $period,
            'expanded'          => true,
            'label'             => ' '
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $app->redirect($app->path('student.top', $form->getData()));
        }

        if ($period && $subject) {
            $result = Grade::getTopByTermAndSubject($period, $subject, $user->id);
        }
        
        return View::render('student/top', array(
            'browse_form'   => $form->createView(),
            'extra_data'    => array('query_result' => $result),
            'period'        => $period,
            'subject'       => $subject,
            'result'        => $result,
        ));
    }

    /**
     * Dashboard user account settings
     * 
     * Url: /student/account
     */
    public function account(Request $request, Application $app)
    {
        $user = Auth::user()->getModel();
        $form = Form::create($user->toArray());

        $form->add('mobile_number', 'text', array(
            'label' => 'Mobile number *',
            'constraints' => new Assert\Regex(array(
                'pattern'   => '/^((0|63|\+63)([\d]{10}))$/',
                'message'   => 'Please enter a valid mobile number. Eg. 09161234567',
                'match'     => true
            ))
        ));

        $form->add('landline', 'text', array(
            'constraints' => new Assert\Regex(array(
                'pattern'   => '/^(([\d]{9})|([\d]{7}))$/',
                'message'   => 'Please enter a valid landline number. Eg. 8001234',
                'match'     => true
            )),

            'required' => false
        ));

        $form->add('email_address', 'text', array(
            'label'       => 'Email address *',
            'constraints' => new Assert\Email()
        ));

        $form->add('address', 'textarea', array(
            'label' => 'Address *'
        ));

        $form->add('guardian_name', 'text', array(
            'label' => 'Name of guardian/parent *'
        ));

        $form->add('guardian_contact_number', 'text', array(
            'label' => 'Guardian\'s/Parent\'s contact no. *',
            'constraints' => new Assert\Regex(array(
                'pattern'   => '/^(([\d]{9})|([\d]{7})|((0|63|\+63)([\d]{10})))$/',
                'message'   => 'Please enter a valid mobile number or landline',
                'match'     => true
            ))
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user->fill($form->getData());
            $user->save();

            FlashBag::add('messages', 'success>>>Your account settings has been updated');
            return $app->redirect($app->path('student.index'));
        }

        return View::render('student/account', array(
            'settings_form' => $form->createView()
        ));
    }
}
