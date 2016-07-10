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
use App\Http\Controllers\Controller;
use App\Models\Grade;

class GradeController extends Controller
{
    /**
     * Show record
     * 
     * @param int $studentId Student ID
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($studentId)
    {
        if (Auth::user()->id != $studentId) {
            abort(403);
        }

        $grade = Grade::where('student_id', $studentId)->get([
            'subject', 'section', 'prelim_grade', 'midterm_grade', 'prefinal_grade', 'final_grade'
        ]);

        return $this->json($grade);
    }
}
