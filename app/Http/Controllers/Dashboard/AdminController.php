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

use Session;
use Illuminate\Http\Request;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use App\Extensions\Constraints as CustomAssert;
use App\Http\Controllers\Controller;
use App\Extensions\Form;
use App\Extensions\Role;
use App\Models\Admin;

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('acl');
    }

    /**
     * Manage admin accounts page
     * 
     * URL: /dashboard/admins/
     */
    public function index(Request $request)
    {
        $form = Form::create($request->query->all());
        
        $form->add('name', Type\TextType::class, [
            'required'  => false
        ]);
        
        $result = Admin::search([
            ['name', 'LIKE', '%' . $request->query->get('name') . '%']
        ]);

        return view('dashboard/admins/index', [
            'search_form'   => $form->getForm()->createView(),
            'result'        => $result
        ]);
    }

    /**
     * Edit admin account page
     * 
     * URL: /dashboard/admins/add
     * URL: /dashboard/admins/{id}/edit
     */
    public function edit(Request $request, Admin $admin = null)
    {
        $mode = $admin->id ? 'edit' : 'add';
        $form = Form::create($admin->toArray());

        $form->add('first_name', Type\TextType::class);
        $form->add('middle_name', Type\TextType::class);
        $form->add('last_name', Type\TextType::class);

        $form->add('username', Type\TextType::class, [
            'constraints' => new CustomAssert\UniqueRecord([
                'model'     => 'App\Models\Admin',
                'exclude'   => $admin->username,
                'row'       => 'username',
                'message'   => 'Username already in use.'
            ])
        ]);

        if ($this->user->id != $admin->id) {
            $form->add('password', Type\RepeatedType::class, [
                'type'      => Type\PasswordType::class,
                'required'  => false,

                'first_options' => ['label' => 'Password (leave blank if not changing)'],
                'second_options' => ['label' => 'Repeat Password (leave blank if not changing)'],

                'constraints' => new Assert\Length([
                    'min'        => 8,
                    'minMessage' => 'Password should be at least 8 characters long'
                ])
            ]);
        }

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if ($this->user->id != $admin->id && $data['password'] === null) {
                unset($data['password']);
            }

            $admin->fill($data);
            $admin->save();

            Session::flash('messages', 'success>>>Admin account has been saved');

            return redirect()->route('dashboard.admins.index');
        }

        return view('dashboard/admins/' . $mode, [
            'form'  => $form->createView(),
            'admin' => $admin
        ]);
    }

    /**
     * Delete admin account page
     * 
     * URL: /dashboard/admins/{id}/delete
     */
    public function delete(Request $request, Admin $admin)
    {
        $form = Form::create();
        $form->add('_confirm', Type\HiddenType::class);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($this->isRole(Role::ADMIN) && $this->user->getModel()->id == $admin->id) {
                Session::flash('flash_message', 'danger>>>You are not allowed to commit suicide');

                return redirect()->route('dashboard.admins.index');
            }
            
            $admin->delete();

            Session::flash('flash_message', 'info>>>Admin account has been deleted');

            return redirect()->route('dashboard.admins.index');
        }

        return view('dashboard/admins/delete', [
            'form'  => $form->createView(),
            'admin' => $admin
        ]);
    }
}
