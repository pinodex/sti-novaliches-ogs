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

use App\Http\Controllers\Controller;

class GradeController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
        $this->middleware('acl');
    }

    /**
     * Grades page index
     * 
     * URL: /dashboard/grades/
     */
    public function index()
    {
        return view('dashboard/grades/index');
    }

    /**
     * Grades compare page redirector
     * 
     * URL: /dashboard/grades/compare
     */
    public function compare()
    {
        return redirect()->route('dashboard.grades.compare.upload');
    }
}
