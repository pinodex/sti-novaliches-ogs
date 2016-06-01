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
use App\Services\Session;
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
        
        $failedLogins = Session::get('failed_logins', 0);
        $secondsLeftUntilUnlock = (300 - (time() - (Session::get('last_failed_login') + 300)));

        if ($secondsLeftUntilUnlock <= 0) {
            Session::set('failed_logins', 0);
            $failedLogins = 0;
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
            if ($failedLogins >= 10) {
                $minutesLeft = ceil($secondsLeftUntilUnlock / 60) . ' minute';

                if ($minutesLeft > 1) {
                    $minutesLeft .= 's';
                }

                Form::flashError('login_form', 'You have exceeded the maximum failed login attempts. Try again after ' . $minutesLeft);
                return $app->redirect($app->path('site.login'));
            }

            $data = $form->getData();

            try {
                $user = Auth::attempt($data['id'], $data['password']);
            } catch (AuthException $e) {
                Form::flashError('login_form', $e->getMessage());

                if ($e->getCode() == AuthException::ACCOUNT_LOCKED) {
                    FlashBag::add('account_locked', true);
                } else {
                    Session::set('failed_logins', ++$failedLogins);
                    Session::set('last_failed_login', time());
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
     * Site credits page
     * 
     * URL: /credits
     */
    public function credits()
    {
        return View::render('credits');
    }
}
