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

        if (!$this->isRole('faculty')) {
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

        if ($this->isRole('admin')) {
            $composeForm = Form::create();

            $composeForm->add('recipient', Type\ChoiceType::class, [
                'choices'       => $facultyChoices,
                'placeholder'   => 'Select recipient'
            ]);
        }
        
        $subject = $request->query->get('subject');
        $adminId = $request->query->get('admin');
        $facultyId = $request->query->get('faculty');
        
        if ($this->isRole('faculty')) {
            $facultyId = $this->user->id;
            $adminId = null;
        }

        return view('dashboard/memos/index', [
            'search_form'   => $searchForm->getForm()->createView(),
            'compose_form'  => $composeForm ? $composeForm->getForm()->createView() : null,
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
            Session::flash('flash_message', 'danger>>>No valid recipient');

            return redirect()->route('dashboard.memo.index');
        }

        if (!$faculty = Faculty::find($recipient)) {
            Session::flash('flash_message', 'danger>>>Invalid recipient');

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

            if ($this->isRole('admin')) {
                $data['admin_id'] = $this->user->getModel()->id;
            }

            $data['faculty_id'] = $faculty->id;

            Memo::create($data);

            Session::flash('flash_message', 'success>>>Memo sent');

            return redirect()->route('dashboard.memo.index');
        }

        return view('dashboard/memos/send', array(
            'form'              => $form->createView(),
            'recipient_name'    => $faculty->name,
            'use_medium_editor' => true
        ));
    }

    /**
     * Memo add page
     * 
     * URL: /dashboard/memos/{id}
     */
    public function view(Request $request, Memo $memo)
    {
        if ($this->isRole('faculty') && $this->user->id != $memo->faculty->id) {
            abort(403);
        }

        if ($this->isRole('faculty') && $memo->opened_at === null) {
            $memo->opened_at = date('Y-m-d H:i:s');
            $memo->save();
        }

        return view('dashboard/memos/view', array(
            'memo' => $memo
        ));
    }
}
