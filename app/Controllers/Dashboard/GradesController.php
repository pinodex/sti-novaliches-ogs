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

use App\Services\View;

/**
 * Route controller for grade pages
 */
class GradesController
{
    /**
     * Grades page index
     * 
     * URL: /dashboard/grades/
     */
    public function index()
    {
        return View::render('dashboard/grades/index');
    }
}
