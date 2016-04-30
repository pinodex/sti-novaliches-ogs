<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers\Admin;

use Silex\Application;
use App\Services\Auth;
use App\Services\View;
use App\Services\Form;
use App\Services\Session\FlashBag;
use App\Constraints as CustomAssert;
use Symfony\Component\HttpFoundation\Request;

/**
 * Admin controller
 * 
 * Route controllers for /admin/
 */
class MainController
{
    /**
     * Admin page index
     * 
     * URL: /admin/
     */
    public function index()
    {
        return View::render('admin/index');
    }

    /**
     * Admin page settings
     * 
     * URL: /admin/settings
     */
    public function settings(Request $request, Application $app)
    {
        $user = Auth::user();
        $form = Form::create();

        $form->add('old_password', 'password', array(
            'required'      => false,
            'constraints'   => new CustomAssert\PasswordMatch(array(
                'hash' => $user->getModel()->password
            ))
        ));

        $form->add('password', 'repeated', array(
            'type'      => 'password',
            'required'  => false,

            'first_options' => array(
                'label' => 'Password (leave blank if not changing)'
            ),

            'second_options' => array(
                'label' => 'Repeat Password (leave blank if not changing)'
            )
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if (!$data['password']) {
                unset($data['password']);
            }

            $user->getModel()->fill($data);
            $user->getModel()->save();

            FlashBag::add('messages', 'success>>>Settings has been saved.');
            return $app->redirect($app->path('admin.settings'));
        }

        return View::render('_shared/settings', array(
            'settings_form' => $form->createView()
        ));
    }
}
