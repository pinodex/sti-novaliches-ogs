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
use App\Services\Session\FlashBag;
use App\Services\Auth;
use App\Services\View;
use App\Services\Form;

/**
 * Dashboard controller
 * 
 * Route controller for dashboard pages.
 */
class MainController
{
    /**
     * Dashboard index
     * 
     * URL: /dashboard/
     */
    public function index(Application $app)
    {
        return View::render('/dashboard/index');
    }

    /**
     * Dashboard user account settings
     * 
     * Url: /dashboard/account
     */
    public function account(Request $request, Application $app)
    {
        $user = Auth::user()->getModel();
        $form = Form::create();

        $form->add('current_password', 'password', array(
            'constraints' => new CustomAssert\PasswordMatch(array(
                'hash' => $user->password
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
            $user->fill($form->getData());
            $user->save();

            FlashBag::add('messages', 'success>>>Your account settings has been updated');
            return $app->redirect($app->path('dashboard.index'));
        }

        return View::render('dashboard/settings', array(
            'settings_form' => $form->createView()
        ));
    }
}
