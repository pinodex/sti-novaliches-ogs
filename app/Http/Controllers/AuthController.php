<?php

/*
 * This file is part of the SIS for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use Auth;
use Password;
use Illuminate\Http\Request;
use App\Http\Forms\LoginForm;
use App\Http\Forms\PasswordResetForm;
use App\Components\Auth\Sso\AuthException;
use App\Components\Auth\Sso\Token as SsoToken;
use App\Components\Auth\Sso\Client as SsoClient;
use App\Components\Ajax;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Login page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function login(Request $request)
    {
        $form = with(new LoginForm)->getForm();

        if ($request->getMethod() == 'POST' && $request->ajax()) {
            $data = $request->only('id', 'password');
            $auth = Auth::attempt($data);

            if (!is_numeric($data['id'])) {
                return [
                    'status'    => Ajax::AUTH_SUCCESS_REDIRECT,
                    'message'   => "Redirecting to Employee login page",
                    'data'      => [
                        'duration'  => 60000,
                        'fold'      => false,
                        'location'  => config('sso.server') . 'authorize?' . http_build_query([
                            'client_id' => config('sso.client_id'),
                            'username'  => $data['id']
                        ])
                    ]
                ];
            }

            if (!$auth) {
                return [
                    'status'    => Ajax::AUTH_FAIL,
                    'message'   => 'Invalid student number and/or password'
                ];
            }

            $user = Auth::user();
            $location = route($user->getRedirectRoute());

            if ($next = $request->query->get('next')) {
                $location = $request->getSchemeAndHttpHost() . '/' . ltrim(urldecode($next), '/');
            }

            return [
                'status'    => Ajax::AUTH_SUCCESS_REDIRECT,
                'message'   => "Hello {$user->name}",
                'data'      => [
                    'fold'      => true,
                    'location'  => $location
                ]
            ];
        }

        return view('auth.login', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Logout action
     * 
     * @return mixed
     */
    public function logout()
    {
        Auth::logout();

        return redirect()->route('auth.login');
    }

    /**
     * SSO Callback page
     * 
     * @param \Illuminate\Http\Request $request Request object
     * 
     * @return mixed
     */
    public function ssoCallback(Request $request)
    {
        $code = $request->input('code');

        $token = new SsoToken(config('sso.client_id'), config('sso.client_secret'), $code);
        $client = new SsoClient($token, config('sso.server'));

        try {
            $identity = $client->login();
        } catch (AuthException $e) {
            return redirect()->route('auth.login')
                ->with('message', ['danger', $e->getMessage()]);
        }

        return 'OK'; // TODO
    }
}
