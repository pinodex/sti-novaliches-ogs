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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Models\Grade;

/**
 * Student controller
 * 
 * Route controllers for student pages (/student/*)
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
        $userModel = Auth::user()->getModel();

        return View::render('student/index', array(
            'student' => $userModel->toArray(),
            'grades' => $userModel->grades->toArray()
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
            throw new NotFoundHttpException('Period not found');
        }

        $user = Auth::user();
        $subjects = $user->getModel()->subjects();

        if ($subject && !in_array($subject, $subjects)) {
            throw new NotFoundHttpException('Subject not found');
        }

        $subjectChoices = array();
        $result = array();

        foreach ($subjects as $choice) {
            $subjectChoices[$choice] = $choice;
        }

        $form = Form::create();

        $form->add('subject', Type\ChoiceType::class, array(
            'choices' => $subjectChoices,
            'data' => $subject
        ));

        $form->add('period', Type\ChoiceType::class, array(
            'choices' => array(
                'Prelim' => 'prelim',
                'Midterm' => 'midterm',
                'Pre-final' => 'prefinal',
                'Final' => 'final'
            ),

            'data' => $period,
            'expanded' => true,
            'label' => ' '
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $app->redirect($app->path('student.top', $form->getData()));
        }

        if ($period && $subject) {
            $result = Grade::getTopByTermAndSubject($period, $subject, $user->getModel()->id);
        }
        
        return View::render('student/top', array(
            'browse_form' => $form->createView(),
            'extra_data' => array('query_result' => $result),
            'period' => $period,
            'subject' => $subject,
            'result' => $result,
        ));
    }
}
