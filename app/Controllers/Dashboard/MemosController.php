<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers\Dashboard;

use Silex\Application;
use App\Models\Memo;
use App\Models\Admin;
use App\Models\Faculty;
use App\Services\View;
use App\Services\Form;
use App\Services\FlashBag;
use App\Controllers\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Illuminate\Database\Eloquent\Builder;

/**
 * Route controller for memos management pages
 */
class MemosController extends Controller
{
    /**
     * Memos index page
     * 
     * URL: /dashboard/memos/
     */
    public function index(Request $request)
    {
        if ($page = $request->query->get('page')) {
            Paginator::currentPageResolver(function () use ($page) {
                return $page;
            });
        }

        $form = Form::create(null, array(
            'csrf_protection' => false
        ));
        
        $form->add('subject', 'text', array(
            'required'  => false,
            'data'      => $request->query->get('subject')
        ));

        $form->add('admin', 'choice', array(
            'label'     => 'By admin',
            'required'  => false,
            'data'      => $request->query->get('admin'),
            'choices'   => Admin::getFormChoices()
        ));

        $form->add('faculty', 'choice', array(
            'label'     => 'To faculty',
            'required'  => false,
            'data'      => $request->query->get('faculty'),
            'choices'   => Faculty::getFormChoices()
        ));

        $form = $form->getForm();
        
        $result = Memo::with('faculty', 'admin')->where(function (Builder $query) use ($request) {
            $query->where('subject', 'LIKE', '%' . $request->query->get('subject') . '%');

            if ($adminId = $request->query->get('admin')) {
                $query->where('admin_id', $admin);
            }

            if ($facultyId = $request->query->get('faculty')) {
                $query->where('faculty_id', $facultyId);
            }
        })->orderBy('id', 'DESC')->paginate();

        return View::render('dashboard/memos/index', array(
            'search_form'   => $form->createView(),
            'result'        => $result->toArray()
        ));
    }

    /**
     * Memo add page
     * 
     * URL: /dashboard/memos/add
     */
    public function add(Request $request, Application $app)
    {
        if (!$recipient = $request->query->get('recipient')) {
            FlashBag::add('messages', 'danger>>>No valid recipient');
            return $app->redirect($app->path('dashboard.memos'));
        }

        if (!$faculty = Faculty::find($recipient)) {
            FlashBag::add('messages', 'danger>>>Invalid recipient');
            return $app->redirect($app->path('dashboard.memos'));
        }

        $form = Form::create();

        $form->add('subject', 'text', array(
            'data' => 'Failure to submit reports and deliverables'
        ));

        $form->add('content', 'textarea', array(
            'attr' => array(
                'class' => 'medium-editable'
            ),

            'data' => View::render('_templates/faculty-memo')
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if ($this->isRole('admin')) {
                $data['admin_id'] = $this->user->getModel()->id;
            }

            $data['faculty_id'] = $faculty->id;

            Memo::create($data);

            FlashBag::add('messages', 'success>>>Memo sent');
            return $app->redirect($app->path('dashboard.memos'));
        }

        return View::render('dashboard/memos/add', array(
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
    public function view(Request $request, Application $app, $id)
    {
        if (!$memo = Memo::with('admin', 'faculty')->find($id)) {
            FlashBag::add('messages', 'danger>>>Cannot find memo');
            return $app->redirect($app->path('dashboard.memos'));
        }

        if ($this->isRole('faculty') && $this->user->getModel()->id != $memo->faculty->id) {
            FlashBag::add('messages', 'danger>>>You don\'t have permission to view this memo');
            return $app->redirect($app->path('dashboard.memos'));
        }

        return View::render('dashboard/memos/view', array(
            'memo' => $memo->toArray()
        ));
    }
}
