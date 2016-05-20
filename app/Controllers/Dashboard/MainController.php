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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use App\Constraints as CustomAssert;
use App\Controllers\Controller;
use App\Services\FlashBag;
use App\Services\View;
use App\Services\Form;

/**
 * Dashboard controller
 * 
 * Route controller for dashboard pages.
 */
class MainController extends Controller
{
    /**
     * Dashboard index
     * 
     * URL: /dashboard/
     */
    public function index(Application $app)
    {
        $context = array();

        if ($this->isRole('faculty')) {
            $faculty = $this->user->getModel();

            $context['faculty'] = $faculty->toArray();
            $context['unread_memo_count'] = $faculty->getUnreadMemoCount();
            $context['department'] = $faculty->department;
            
            $context['statuses'] = array(
                $faculty->getStatusAttribute('prelim'),
                $faculty->getStatusAttribute('midterm'),
                $faculty->getStatusAttribute('prefinal'),
                $faculty->getStatusAttribute('final')
            );

            $context['stats'] = array(
                'failed' => array(
                    'prelim'    => $faculty->getNumberOfFailsAttribute('prelim'),
                    'midterm'   => $faculty->getNumberOfFailsAttribute('midterm'),
                    'prefinal'  => $faculty->getNumberOfFailsAttribute('prefinal'),
                    'final'     => $faculty->getNumberOfFailsAttribute('final')
                ),

                'dropped' => array(
                    'prelim'    => $faculty->getNumberOfDropsAttribute('prelim'),
                    'midterm'   => $faculty->getNumberOfDropsAttribute('midterm'),
                    'prefinal'  => $faculty->getNumberOfDropsAttribute('prefinal'),
                    'final'     => $faculty->getNumberOfDropsAttribute('final'),
                )
            );
        }

        return View::render('/dashboard/index', $context);
    }

    /**
     * Dashboard user account settings
     * 
     * Url: /dashboard/account
     */
    public function account(Request $request, Application $app)
    {
        $form = Form::create();

        $form->add('current_password', 'password', array(
            'constraints' => new CustomAssert\PasswordMatch(array(
                'hash' => $this->user->getModel()->password
            ))
        ));

        $form->add('password', 'repeated', array(
            'type'              => 'password',
            'first_options'     => array('label' => 'New Password'),
            'second_options'    => array('label' => 'Repeat Password'),
            
            'constraints'       => new Assert\Length(array(
                'min'           => 8,
                'minMessage'    => 'Password should be at least 8 characters long.'
            ))
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->user->getModel()->fill($form->getData());
            $this->user->getModel()->save();

            FlashBag::add('messages', 'success>>>Your account settings has been updated');
            return $app->redirect($app->path('dashboard.index'));
        }

        return View::render('dashboard/account', array(
            'settings_form' => $form->createView()
        ));
    }
}
