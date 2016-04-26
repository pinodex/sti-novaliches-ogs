<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers\Faculty;

use App\Models\Student;
use App\Services\Form;
use App\Services\View;
use App\Services\Helper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type;
use Illuminate\Pagination\Paginator;

/**
 * Faculty maion controller
 * 
 * Route controllers for /faculty/
 */
class MainController
{
    /**
     * Faculty page index
     * 
     * URL: /faculty/
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
        
        $form->add('id', Type\TextType::class, array(
            'label' => 'Search by number',
            'required' => false,
            'data' => $request->query->get('id')
        ));
        
        $form->add('name', Type\TextType::class, array(
            'label' => 'Search by name',
            'required' => false,
            'data' => $request->query->get('name')
        ));

        $result = array();
        $form = $form->getForm();

        $request->query->set('id', Helper::parseId(
            $request->query->get('id')
        ));

        $result = Student::search(
            $request->query->get('id'),
            $request->query->get('name')
        );

        return View::render('faculty/index', array(
            'search_form' => $form->createView(),
            'current_page' => $result->currentPage(),
            'last_page' => $result->lastPage(),
            'result' => $result
        ));
    }
}
