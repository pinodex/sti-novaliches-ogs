<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use App\Extensions\Constraints as CustomAssert;
use App\Extensions\Alert;
use App\Extensions\Form;
use App\Extensions\Role;

class MainController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('acl');
    }

    /**
     * Dashboard index
     * 
     * URL: /dashboard/
     */
    public function index()
    {
        $context = [];

        if ($this->isRole(Role::FACULTY)) {
            $faculty = $this->user->getModel();

            $context['faculty'] = $faculty->toArray();
            $context['unread_memo_count'] = $faculty->getUnreadMemoCount();
            $context['department'] = $faculty->department;
        }

        return view('/dashboard/index', $context);
    }

    /**
     * Dashboard user account settings
     * 
     * Url: /dashboard/account
     */
    public function account(Request $request)
    {
        $form = Form::create();

        $form->add('current_password', Type\PasswordType::class, [
            'constraints' => new CustomAssert\PasswordMatch([
                'hash' => $this->user->password
            ])
        ]);

        $form->add('password', Type\RepeatedType::class, [
            'type'              => Type\PasswordType::class,
            'first_options'     => ['label' => 'New Password'],
            'second_options'    => ['label' => 'Repeat Password'],
            
            'constraints'       => new Assert\Length([
                'min'           => 8,
                'minMessage'    => 'Password should be at least 8 characters long.'
            ])
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->user->getModel()->fill($form->getData());
            $this->user->getModel()->save();

            Alert::success('Your account settings has been updated');

            return redirect()->route('dashboard.index');
        }

        return view('dashboard/account', [
            'settings_form' => $form->createView()
        ]);
    }
}
