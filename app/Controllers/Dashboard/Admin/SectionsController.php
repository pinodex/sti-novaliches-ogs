<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers\Dashboard\Admin;

use Silex\Application;
use App\Models\Section;
use App\Models\Faculty;
use App\Services\View;
use App\Services\Form;
use App\Services\Session\FlashBag;
use App\Constraints as CustomAssert;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Route controller for administrator management pages
 */
class SectionsController
{
    /**
     * Manage admin accounts page
     * 
     * URL: /dashboard/sections/
     */
    public function index(Request $request)
    {
        if ($page = $request->query->get('page')) {
            Paginator::currentPageResolver(function() use($page) {
                return $page;
            });
        }

        $form = Form::create(null, array(
            'csrf_protection' => false
        ));
        
        $form->add('id', 'text', array(
            'label'     => 'Name',
            'required'  => false,
            'data'      => $request->query->get('name')
        ));

        $form = $form->getForm();

        if ($nameQuery = $request->query->get('name')) {
            $result = Section::where('id', 'LIKE', '%' . $request->query->get('name') . '%')
                ->paginate(50);
        } else {
            $result = Section::paginate(50);
        }

        return View::render('dashboard/sections/index', array(
            'search_form'   => $form->createView(),
            'result'        => $result->toArray()
        ));
    }

    /**
     * Edit admin account page
     * 
     * URL: /dashboard/sections/add
     * URL: /dashboard/sections/{id}/edit
     */
    public function edit(Request $request, Application $app, $id)
    {
        $mode = 'add';
        $section = Section::findOrNew($id);

        if ($section->id != $id) {
            FlashBag::add('messages', 'danger>>>Section not found');
            return $app->redirect($app->path('dashboard.sections'));
        }

        $id && $mode = 'edit';
        $form = Form::create($section->toArray());

        $form->add('id', 'text', array(
            'label'         => 'Name',
            'constraints'   => new CustomAssert\UniqueRecord(array(
                'model'         => 'App\Models\Section',
                'exclude'       => $section->id,
                'comparator'    => 'LIKE',
                'row'           => 'id',
                'message'       => 'Section already added.'
            ))
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $data['id'] = strtoupper($data['id']);

            $section->faculties->each(function (Faculty $item) use ($section, $data) {
                // Replace old value to new value from the many-to-many relation table.
                $item->sections()->detach($section->id);
                $item->sections()->attach($data['id']);
            });

            $section->fill($data);
            $section->save();

            FlashBag::add('messages', 'success>>>Section has been saved');

            return $app->redirect($app->path('dashboard.sections'));
        }

        return View::render('dashboard/sections/' . $mode, array(
            'form'      => $form->createView(),
            'section'   => $section->toArray()
        ));
    }

    /**
     * Delete admin account page
     * 
     * URL: /dashboard/sections/{id}/delete
     */
    public function delete(Request $request, Application $app, $id)
    {
        if (!$section = Section::find($id)) {
            FlashBag::add('messages', 'danger>>>Section not found');

            return $app->redirect($app->path('dashboard.sections'));
        }

        $form = Form::create();
        $form->add('_confirm', 'hidden');

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $section->delete();

            FlashBag::add('messages', 'info>>>Section has been deleted');

            return $app->redirect($app->path('dashboard.sections'));
        }

        return View::render('dashboard/sections/delete', array(
            'form'      => $form->createView(),
            'section'   => $section->toArray()
        ));
    } 
}
