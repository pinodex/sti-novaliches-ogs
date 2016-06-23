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

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Grade;

/**
 * Main controller
 * 
 * Route controller for main pages.
 * Includes the root index and the login/logout routes
 */
class GradeController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show($studentId)
    {
        $grade = Grade::where('student_id', $studentId)->get([
            'subject', 'section', 'prelim_grade', 'midterm_grade', 'prefinal_grade', 'final_grade'
        ]);

        return response()->json($grade);
    }
}
