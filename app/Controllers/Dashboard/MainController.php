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
use Symfony\Component\HttpFoundation\Request;
use App\Services\View;

/**
 * Dashboard controller
 * 
 * Route controller for dashboard pages.
 */
class MainController
{
    /**
     * Dashboard index
     * 
     * URL: /
     */
    public function index(Application $app)
    {
        return View::render('/dashboard/index');
    }
}
