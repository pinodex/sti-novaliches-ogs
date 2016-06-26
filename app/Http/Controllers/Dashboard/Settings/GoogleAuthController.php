<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Dashboard\Settings;

use Session;
use Storage;
use Illuminate\Http\Request;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use App\Http\Controllers\Controller;
use App\Extensions\Settings;
use App\Extensions\Form;

class GoogleAuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('acl');
    }

    public function index()
    {
        $client = app('google');
        $userInfo = null;

        if ($client->getAccessToken()) {
            $user = $client->getService(\Google_Service_Oauth2::class);

            try {
                $userInfo = $user->userinfo->get();
            } catch (\Exception $e) {
                Session::flash('flash_message', 'warning>>>' . $e->getMessage());
            }
        }

        if (!file_exists(storage_path('app/client_secret.json'))) {
            return redirect()->route('dashboard.settings.googleauth.clientSecret');
        }

        return view('dashboard/settings/googleauth/index', [
            'user_info' => $userInfo
        ]);
    }

    public function clientSecret(Request $request)
    {
        $identifer = null;

        if ($hasClientSecret = file_exists(storage_path('app/client_secret.json')) &&
            $parsedData = json_decode(file_get_contents(storage_path('app/client_secret.json')))) {

            if (isset($parsedData->web->project_id) && isset($parsedData->web->client_id)) {
                $identifer = sprintf('%s@%s',
                    $parsedData->web->project_id,
                    $parsedData->web->client_id
                );
            }
        }

        $form = Form::create();

        $form->add('client_secret_file', Type\FileType::class, [
            'label' => ' ',
            'constraints' => new Assert\File([
                'mimeTypesMessage' => 'Please upload a JSON file',
                'mimeTypes' => [
                    'application/json',
                    'text/plain',
                    'text/json'
                ]
            ])
        ]);


        $form = $form->getForm();
        $form->handleRequest($request);

        Form::handleFlashErrors('upload_form', $form);

        if ($form->isValid()) {
            $file = $form['client_secret_file']->getData();
            
            json_decode(file_get_contents($file->getPathname()));

            if (json_last_error() !== JSON_ERROR_NONE) {
                Form::flashError('upload_form', 'JSON file error: ' . json_last_error_msg());

                return redirect()->route('dashboard.settings.googleauth.clientSecret');
            }

            Session::flash('flash_message', 'success>>>Client secret file has been saved');
            Storage::put('client_secret.json', file_get_contents($file->getPathname()));

            return redirect()->route('dashboard.settings.googleauth.clientSecret');
        }

        return view('dashboard/settings/googleauth/client-secret', [
            'has_client_secret' => $hasClientSecret,
            'identifer'         => $identifer,
            'upload_form'       => $form->createView()
        ]);
    }

    public function connect(Request $request)
    {
        $client = app('google');
        
        $client->addScope(\Google_Service_Oauth2::USERINFO_PROFILE);
        $client->addScope(\Google_Service_Oauth2::USERINFO_EMAIL);
        $client->addScope(\Google_Service_Gmail::GMAIL_SEND);

        $client->setRedirectUri($request->getSchemeAndHttpHost() . $request->getPathinfo());

        if ($request->query->get('error')) {
            Session::flash('flash_message', 'danger>>>Authorization error');

            return redirect()->route('dashboard.settings.googleauth.index');
        }

        if ($code = $request->query->get('code')) {
            try {
                $token = $client->authenticate($code);
            } catch (\Google_Auth_Exception $e) {
                Session::flash('flash_message', 'danger>>>' . $e->getMessage());

                return redirect()->route('dashboard.settings.googleauth.index');
            }

            Settings::set('google_access_token', $token);

            return redirect()->route('dashboard.settings.googleauth.index');
        }

        return redirect($client->createAuthUrl());
    }

    public function disconnect()
    {
        Settings::remove('google_access_token');

        return redirect()->route('dashboard.settings.googleauth.index');        
    }
}
