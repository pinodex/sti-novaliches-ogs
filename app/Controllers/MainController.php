<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers;

use Silex\Application;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Form;
use App\Services\View;
use App\Services\FlashBag;
use App\Exceptions\AuthException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Main controller
 * 
 * Route controller for main pages.
 * Includes the root index and the login/logout routes
 */
class MainController extends Controller
{
    /**
     * Site root index
     * 
     * URL: /
     */
    public function index(Application $app)
    {
        $destination = 'site.login';
        
        if ($this->user) {
            $destination = $this->user->getProvider()->getRedirectRoute();
        }

        return $app->redirect($app->path($destination));
    }

    /**
     * Login page
     * 
     * URL: /login
     */
    public function login(Request $request, Application $app)
    {
        if ($this->isLoggedIn()) {
            return $app->redirect($app->path('site.index'));
        }

        $form = Form::create();
        
        $form->add('id', 'text', array(
            'attr' => array(
                'autofocus'     => true,
                'placeholder'   => 'Student Number'
            )
        ));
        
        $form->add('password', 'password', array(
            'attr' => array(
                'placeholder' => 'Password'
            )
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        Form::handleFlashErrors('login_form', $form);

        if ($form->isValid()) {
            $data = $form->getData();

            try {
                $user = Auth::attempt($data['id'], $data['password']);
            } catch (AuthException $e) {
                Form::flashError('login_form', $e->getMessage());

                if ($e->getCode() == AuthException::ACCOUNT_LOCKED) {
                    FlashBag::add('account_locked', true);
                }

                if ($next = $request->query->get('next')) {
                    return $app->redirect($app->path('site.login', array(
                        'next' => $next
                    )));
                }

                return $app->redirect($app->path('site.login'));
            }

            Auth::login($user);

            if ($next = $request->query->get('next')) {
                // Prevent open-redirect
                return $app->redirect($request->getSchemeAndHttpHost() . urldecode($next));
            }

            return $app->redirect($app->path(
                $user->getProvider()->getRedirectRoute()
            ));
        }

        return View::render('login', array(
            'login_form' => $form->createView()
        ));
    }

    /**
     * Logout page
     * 
     * URL: /logout
     */
    public function logout(Request $request, Application $app)
    {
        if (Csrf::isValid('logout', $request->query->get('_token'))) {
            Auth::logout();
        }

        return $app->redirect($app->path('site.login'));
    }

    /**
     * Account settings redirector
     * 
     * URL: /settings
     */
    public function settings(Application $app)
    {
        $destination = 'site.login';
        
        if ($this->user) {
            $role = $this->user->getProvider()->getRole();

            switch ($role) {
                case 'admin':
                    $destination = 'admin.settings';
                    break;
                
                case 'faculty':
                    $destination = 'faculty.settings';
                    break;
            }
        }

        return $app->redirect($app->path($destination));
    }
}
