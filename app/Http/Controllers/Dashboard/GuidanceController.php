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
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use App\Extensions\Constraints as CustomAssert;
use App\Http\Controllers\Controller;
use App\Extensions\Alert;
use App\Extensions\Form;
use App\Models\Guidance;

class GuidanceController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('acl');
    }

    /**
     * Manage guidance accounts page
     * 
     * URL: /dashboard/guidance/
     */
    public function index(Request $request)
    {
        $form = Form::create($request->query->all());
        
        $form->add('name', Type\TextType::class, [
            'label'     => 'Name',
            'required'  => false
        ]);
        
        $result = Guidance::search([['name', 'LIKE', '%' . $request->query->get('name') . '%']]);

        return view('dashboard/guidance/index', [
            'search_form'   => $form->getForm()->createView(),
            'result'        => $result
        ]);
    }

    /**
     * Edit guidance account page
     * 
     * URL: /dashboard/guidance/add
     * URL: /dashboard/guidance/{id}/edit
     */
    public function edit(Request $request, Guidance $guidance = null)
    {
        $mode = $guidance->id ? 'edit' : 'add';
        $form = Form::create($guidance->toArray());

        $form->add('first_name', Type\TextType::class);
        $form->add('middle_name', Type\TextType::class);
        $form->add('last_name', Type\TextType::class);

        $form->add('username', Type\TextType::class, [
            'constraints' => new CustomAssert\UniqueRecord([
                'model'     => Guidance::class,
                'exclude'   => $guidance->username,
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
            
            if ($data['password'] === null) {
                unset($data['password']);
            }

            $guidance->fill($data);
            $guidance->save();

            if ($mode == 'add') {
                Alert::success("<strong>{$guidance->name}</strong> has been added to guidance accounts");
            }

            if ($mode == 'edit') {
                Alert::success("Changes to <strong>{$guidance->name}</strong> has been saved");
            }

            return redirect()->route('dashboard.guidance.index');
        }

        return view('dashboard/guidance/' . $mode, [
            'form' => $form->createView(),
            'guidance' => $guidance
        ]);
    }

    /**
     * Delete guidance account page
     * 
     * URL: /dashboard/guidance/{id}/delete
     */
    public function delete(Request $request, Guidance $guidance)
    {
        $form = Form::create();
        $form->add('_confirm', Type\HiddenType::class);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $guidance->delete();

            Alert::info("<strong>{$guidance->name}</strong> account has been deleted");

            return redirect()->route('dashboard.guidance.index');
        }

        return view('dashboard/guidance/delete', [
            'form' => $form->createView(),
            'guidance' => $guidance
        ]);
    }
}
