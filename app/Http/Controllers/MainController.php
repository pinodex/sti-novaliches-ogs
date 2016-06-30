<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Symfony\Component\Form\Extension\Core\Type;
use App\Exceptions\AuthException;
use App\Extensions\Form;

class MainController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $this->middleware('guest', [
            'except' => ['logout', 'credits']
        ]);
    }

    /**
     * Index page
     * 
     * URL: /
     */
    public function index()
    {
        return redirect()->route('login');
    }

    /**
     * Login page
     * 
     * URL: /login
     */
    public function login(Request $request)
    {
        $failedAttempts = session('failed_attempts') ?: 0;
        $lastFailedAttempt = session('last_failed_attempt');
        $throttleSecondsLeft = (300 - (time() - ($lastFailedAttempt + 300)));

        if ($throttleSecondsLeft <= 0) {
            session()->forget('failed_attempts');
            session()->forget('last_failed_attempt');

            $failedAttempts = 0;
        }

        $form = Form::create();

        $form->add('id', Type\TextType::class, [
            'attr' => [
                'autofocus'     => true,
                'placeholder'   => 'Student Number'
            ]
        ]);
        
        $form->add('password', Type\PasswordType::class, [
            'attr' => [
                'placeholder' => 'Password'
            ]
        ]);

        if ($failedAttempts >= 10) {
            Form::flashError('login_form',
                sprintf('You exceeded the maximum failed login attempts. Please try again after %s %s.',
                    ceil($throttleSecondsLeft / 60),
                    ceil($throttleSecondsLeft / 60) == 1 ? 'minute' : 'minutes'
                )
            );
        }

        $form = $form->getForm();
        $form->handleRequest($request);
        
        Form::handleFlashErrors('login_form', $form);

        if ($form->isValid()) {
            if ($failedAttempts >= 10) {
                return redirect('login');
            }

            try {
                if (Auth::attempt($form->getData())) {
                    if ($next = $request->query->get('next')) {
                        return redirect($request->getSchemeAndHttpHost() . '/' . ltrim(urldecode($next), '/'));
                    }

                    return redirect()->route(Auth::user()->getRedirectRoute());
                }
            } catch (AuthException $e) {
                Form::flashError('login_form', $e->getMessage());

                if ($e->getCode() == AuthException::ACCOUNT_LOCKED) {
                    session()->flash('account_locked', true);
                }

                session()->put('failed_attempts', ++$failedAttempts);
                session()->put('last_failed_attempt', time());

                return redirect('login');
            }

            Form::flashError('login_form', 'Unknown error occurred');
            return redirect('login');
        }

        return view('login', [
            'login_form' => $form->createView()
        ]);
    }

    /**
     * Logout page
     * 
     * URL: /logout
     */
    public function logout()
    {
        Auth::logout();

        Form::flashError('login_form', 'You have been logged out.');

        return redirect('login');
    }

    /**
     * Credits page
     * 
     * URL: /credits
     */
    public function credits()
    {
        return view('credits');
    }
}
