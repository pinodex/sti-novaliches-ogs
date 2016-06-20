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
use App\Models\Department;
use App\Models\Head;

class HeadController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Manage head accounts page
     * 
     * URL: /dashboard/heads/
     */
    public function index(Request $request)
    {
        $form = Form::create($request->query->all());
        
        $form->add('name', Type\TextType::class, array(
            'required'  => false
        ));
        
        $result = Head::search(
            [['name', 'LIKE', '%' . $request->query->get('name') . '%']],
            ['department']
        );

        return view('dashboard/heads/index', [
            'search_form'   => $form->getForm()->createView(),
            'result'        => $result
        ]);
    }

    /**
     * Edit head account page
     * 
     * URL: /dashboard/heads/add
     * URL: /dashboard/heads/{id}/edit
     */
    public function edit(Request $request, Head $head = null)
    {
        $mode = $head->id ? 'edit' : 'add';
        $form = Form::create($head->toArray());
        
        $departments = Department::getFormChoices();
        $departments['0'] = 'Unassigned';

        $form->add('first_name', Type\TextType::class);
        $form->add('middle_name', Type\TextType::class);
        $form->add('last_name', Type\TextType::class);

        $form->add('department_id', Type\ChoiceType::class, [
            'label'         => 'Department',
            'choices'       => array_flip($departments),
            'data'          => $head->department_id ?: '0',

            'constraints'   => new CustomAssert\UniqueRecord([
                'model'     => Head::class,
                'exclude'   => $head->department_id,
                'row'       => 'department_id',
                'message'   => 'This department has head already assigned.'
            ])
        ]);

        $form->add('username', Type\TextType::class, [
            'constraints' => new CustomAssert\UniqueRecord([
                'model'     => Head::class,
                'exclude'   => $head->username,
                'row'       => 'username',
                'message'   => 'Username already in use.'
            ])
        ]);

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

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if ($data['department_id'] == 0) {
                $data['department_id'] = null;
            }

            if ($data['password'] === null) {
                unset($data['password']);
            }

            $head->fill($data);
            $head->save();

            Session::flash('flash_message', 'success>>>Head account has been saved');

            return redirect()->route('dashboard.heads.index');
        }

        return view('dashboard/heads/' . $mode, [
            'form' => $form->createView(),
            'head' => $head
        ]);
    }

    /**
     * Delete head account page
     * 
     * URL: /dashboard/heads/{id}/delete
     */
    public function delete(Request $request, Head $head)
    {
        $form = Form::create();
        $form->add('_confirm', Type\HiddenType::class);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $head->delete();
            
            Session::flash('flash_message', 'info>>>Head account has been deleted');

            return redirect()->route('dashboard.heads.index');
        }

        return view('dashboard/heads/delete', [
            'form' => $form->createView(),
            'head' => $head
        ]);
    }
}
