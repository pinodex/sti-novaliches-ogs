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
use App\Http\Controllers\Controller;
use App\Extensions\Alert;
use App\Extensions\Form;
use App\Extensions\Role;
use App\Models\Faculty;
use App\Models\Admin;
use App\Models\Memo;

class MemoController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('acl');
    }

    /**
     * Memos index page
     * 
     * URL: /dashboard/memos/
     */
    public function index(Request $request)
    {
        $facultyChoices = array_flip(Faculty::getFormChoices());

        $searchForm = Form::create($request->query->all());
        
        $searchForm->add('subject', Type\TextType::class, [
            'required'  => false
        ]);

        if (!$this->isRole(Role::FACULTY)) {
            $searchForm->add('admin', Type\ChoiceType::class, [
                'label'     => 'By admin',
                'required'  => false,
                'choices'   => array_flip(Admin::getFormChoices())
            ]);

            $searchForm->add('faculty', Type\ChoiceType::class, [
                'label'     => 'To faculty',
                'required'  => false,
                'choices'   => $facultyChoices
            ]);
        }

        if ($this->isRole(Role::ADMIN)) {
            $composeForm = Form::create();

            $composeForm->add('recipient', Type\ChoiceType::class, [
                'choices'       => $facultyChoices,
                'placeholder'   => 'Select recipient'
            ]);
        }
        
        $subject = $request->query->get('subject');
        $adminId = $request->query->get('admin');
        $facultyId = $request->query->get('faculty');
        
        if ($this->isRole(Role::FACULTY)) {
            $facultyId = $this->user->id;
            $adminId = null;
        }

        return view('dashboard/memos/index', [
            'search_form'   => $searchForm->getForm()->createView(),
            'compose_form'  => isset($composeForm) ? $composeForm->getForm()->createView() : null,
            'result'        => Memo::search($subject, $adminId, $facultyId)
        ]);
    }

    /**
     * Memo add page
     * 
     * URL: /dashboard/memos/send
     */
    public function send(Request $request)
    {
        if (!$recipient = $request->query->get('recipient')) {
            Alert::danger('No valid recipient');

            return redirect()->route('dashboard.memo.index');
        }

        if (!$faculty = Faculty::find($recipient)) {
            Alert::danger('Invalid recipient');

            return redirect()->route('dashboard.memo.index');
        }

        $form = Form::create();

        $form->add('subject', Type\TextType::class, [
            'data' => 'Failure to submit reports and deliverables'
        ]);

        $form->add('content', Type\TextareaType::class, [
            'attr' => [
                'class' => 'medium-editable'
            ],

            'data' => view('_templates/faculty-memo', [
                'to'    => $faculty->name,
                'from'  => isset($faculty->department->head->name) ? $faculty->department->head->name : 'Department Head',
                'date'  => date('F j, Y')
            ])
        ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if ($this->isRole(Role::ADMIN)) {
                $data['admin_id'] = $this->user->getModel()->id;
            }

            $data['faculty_id'] = $faculty->id;

            Memo::create($data);

            Alert::success("Memo has been successfully sent to <strong>{$faculty->name}</strong>");

            return redirect()->route('dashboard.memo.index');
        }

        return view('dashboard/memos/send', [
            'form'              => $form->createView(),
            'recipient_name'    => $faculty->name,
            'use_medium_editor' => true
        ]);
    }

    /**
     * Memo add page
     * 
     * URL: /dashboard/memos/{id}
     */
    public function view(Request $request, Memo $memo)
    {
        if ($this->isRole(Role::FACULTY) && $this->user->id != $memo->faculty->id) {
            abort(403);
        }

        if ($this->isRole(Role::FACULTY) && $memo->opened_at === null) {
            $memo->opened_at = date('Y-m-d H:i:s');
            $memo->save();
        }

        return view('dashboard/memos/view', [
            'memo' => $memo
        ]);
    }
}
