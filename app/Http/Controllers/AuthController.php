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
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormError;
use App\Exceptions\AuthException;
use App\Extensions\Form;

class AuthController extends Controller
{
    use ThrottlesLogins;

    public function __construct()
    {
        parent::__construct();
        
        $this->middleware('guest', [
            'except' => ['logout']
        ]);
    }

    public function loginUsername()
    {
        return 'id';
    }

    public function getThrottleKey(Request $request)
    {
        return $request->fingerprint();
    }

    /**
     * Login page
     * 
     * URL: /login
     */
    public function login(Request $request)
    {
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

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($this->hasTooManyLoginAttempts($request)) {
            $secondsRemaining = $this->secondsRemainingOnLockout($request);

            $form->addError(new FormError($this->getLockoutErrorMessage($secondsRemaining)));

            if ($request->getMethod() == 'POST') {
                return redirect()->route('auth.login');
            }
        }
        
        Form::handleFlashErrors('login_form', $form);

        if ($form->isValid()) {
            try {
                if (Auth::attempt($form->getData())) {
                    if ($next = $request->query->get('next')) {
                        return redirect($request->getSchemeAndHttpHost() . '/' . ltrim(urldecode($next), '/'));
                    }

                    $this->clearLoginAttempts($request);
                    return redirect()->route(Auth::user()->getRedirectRoute());
                }
            } catch (AuthException $e) {
                $this->incrementLoginAttempts($request);

                if (!$this->hasTooManyLoginAttempts($request)) {
                    Form::flashError('login_form', $e->getMessage());
                }

                if ($e->getCode() == AuthException::ACCOUNT_LOCKED) {
                    session()->flash('account_locked', true);
                }

                return redirect()->route('auth.login');
            }

            Form::flashError('login_form', 'Unknown error occurred');
            return redirect()->route('auth.login');
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

        return redirect()->route('auth.login');
    }
}
