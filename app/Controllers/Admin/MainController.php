<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers\Admin;

use App\Services\View;

/**
 * Admin controller
 * 
 * Route controllers for /admin/
 */
class MainController
{
    /**
     * Admin page index
     * 
     * URL: /admin/
     */
    public function index()
    {
        return View::render('admin/index');
    }
}
