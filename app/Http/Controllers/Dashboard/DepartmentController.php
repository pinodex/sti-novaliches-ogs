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
use App\Http\Controllers\Controller;
use App\Extensions\Form;
use App\Extensions\Role;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Head;

class DepartmentController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('acl');
    }

    /**
     * Admin departments page index
     * 
     * URL: /dashboard/departments/
     */
    public function index()
    {
        return view('dashboard/departments/index', [
            'departments' => Department::with('head')->get()
        ]);
    }

    /**
     * My department page
     * 
     * URL: /dashboard/departments/self
     */
    public function self()
    {
        if (!$this->areRoles(Role::HEAD, Role::FACULTY)) {
            abort(404);
        }

        if ($this->isRole(Role::HEAD) && $this->user->department === null) {
            Session::flash('flash_message', 'danger>>>You are not yet assigned to any department');

            return redirect()->route('dashboard.index');
        }

        return redirect()->route('dashboard.departments.view', [
            'id' => $this->user->department->id
        ]);
    }

    /**
     * Admin department view page
     * 
     * URL: /dashboard/departments/{id}
     */
    public function view(Request $request, Department $department)
    {
        if ($this->isRole(Role::HEAD) && $this->user->department === null ||
            ($this->user->department !== null && $this->user->department->id != $department->id)) {
            
            abort(403);
        }

        $form = Form::create($request->query->all());
        
        $form->add('name', Type\TextType::class, [
            'label'     => 'Name',
            'required'  => false
        ]);

        $form = $form->getForm();
        
        $faculty = Faculty::search([
            ['department_id', '=', $department->id],
            ['name', 'LIKE', '%' . $request->query->get('name') . '%']
        ]);

        return view('dashboard/departments/view', [
            'search_form'   => $form->createView(),
            'department'    => $department,
            'result'        => $faculty
        ]);
    }

    /**
     * Admin departments page edit
     * 
     * URL: /dashboard/departments/add
     * URL: /dashboard/departments/{id}/edit
     */
    public function edit(Request $request, Department $department)
    {
        $mode = $department->id ? 'edit' : 'add';
        $form = Form::create($department->toArray());

        $heads = Head::getFormChoices();
        $heads['0'] = 'No assignment';

        $form->add('name', Type\TextType::class, [
            'label' => 'Department Name'
        ]);

        $form->add('head', Type\ChoiceType::class, [
            'choices'   => array_flip($heads),
            'data'      => $department->head ? $department->head->id : 0
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $department->name = $data['name'];
            $department->save();

            if ($data['head'] != 0) {
                $head = Head::find($data['head']);
                
                $head->department_id = $department->id;
                $head->save();
            } else {
                $head = Head::where('department_id', $department->id)->update([
                    'department_id' => null
                ]);
            }

            Session::flash('flash_message', 'success>>>Department has been saved');

            if ($mode == 'add') {
                return redirect()->route('dashboard.departments.index');
            }
            
            return redirect()->route('dashboard.departments.view', [
                'id' => $department->id
            ]);
        }

        return view('dashboard/departments/' . $mode, [
            'department'    => $department,
            'form'          => $form->createView()
        ]);
    }

    /**
     * Delete department page
     * 
     * URL: /dashboard/departments/{id}/delete
     */
    public function delete(Request $request, Department $department)
    {
        $form = Form::create();
        $form->add('_confirm', Type\HiddenType::class);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $department->delete();

            Session::flash('flash_message', 'info>>>Department has been deleted');

            return redirect()->route('dashboard.departments.index');
        }

        return view('dashboard/departments/delete', [
            'department'    => $department,
            'form'          => $form->createView()
        ]);
    }
}
