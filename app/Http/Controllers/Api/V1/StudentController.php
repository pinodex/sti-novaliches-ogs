<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Api\V1;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Student;

/**
 * Main controller
 * 
 * Route controller for main pages.
 * Includes the root index and the login/logout routes
 */
class StudentController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show(Student $student)
    {
        if ($student->id != Auth::user()->id) {
            abort(403);
        }

        return response()->json($student);
    }
}
