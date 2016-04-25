<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers;

use Silex\Application;
use App\Services\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Admin controller
 * 
 * Route controllers for admin pages (/admin/*)
 */
class AdminController
{
    /**
     * Admin page index
     * 
     * URL: /admin/
     */
    public function index(Request $request, Application $app)
    {
        return View::render('admin/index');
    }
}
