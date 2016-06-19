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
use FormFactory;
use Illuminate\Http\Request;
use App\Extensions\Constraints as CustomAssert;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use App\Http\Controllers\Controller;
use App\Extensions\Form;
use App\Models\Admin;

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Manage admin accounts page
     * 
     * URL: /dashboard/admins/
     */
    public function index(Request $request)
    {
        $context = array();

        $form = Form::create($request->query->all());
        
        $form->add('name', Type\TextType::class, array(
            'required'  => false
        ));

        $context['search_form'] = $form->getForm()->createView();
        
        $context['result'] = Admin::search(array(
            array('name', 'LIKE', '%' . $request->query->get('name') . '%')
        ))->toArray();

        return view('dashboard/admins/index', $context);
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

        $form->add('username', Type\TextType::class, array(
            'constraints' => new CustomAssert\UniqueRecord(array(
                'model'     => 'App\Models\Admin',
                'exclude'   => $admin->username,
                'row'       => 'username',
                'message'   => 'Username already in use.'
            ))
        ));

        $form->add('password', Type\RepeatedType::class, array(
            'type'      => Type\PasswordType::class,
            'required'  => false,

            'first_options' => array(
                'label' => 'Password (leave blank if not changing)'
            ),

            'second_options' => array(
                'label' => 'Repeat Password (leave blank if not changing)'
            ),

            'constraints' => new Assert\Length(array(
                'min'        => 8,
                'minMessage' => 'Password should be at least 8 characters long'
            ))
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if ($data['password'] === null) {
                unset($data['password']);
            }

            $admin->fill($data);
            $admin->save();

            Session::flash('messages', 'success>>>Admin account has been saved');

            return redirect()->route('dashboard.admins.index');
        }

        return view('dashboard/admins/' . $mode, array(
            'form'  => $form->createView(),
            'admin' => $admin->toArray()
        ));
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
            if ($this->isRole('admin') && $this->user->getModel()->id == $admin->id) {
                Session::flash('flash_message', 'danger>>>You are not allowed to commit suicide');

                return redirect()->route('dashboard.admins.index');
            }
            
            $admin->delete();

            Session::flash('flash_message', 'info>>>Admin account has been deleted');

            return redirect()->route('dashboard.admins.index');;
        }

        return view('dashboard/admins/delete', array(
            'form'  => $form->createView(),
            'admin' => $admin->toArray()
        ));
    }
}
