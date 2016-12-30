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
use App\Models\Student;

class StudentController extends Controller
{
    /**
     * Show record
     * 
     * @param Student $student Student model
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        $student = Student::find($id);

        if ($student->canBeViewedBy($user)) {
            return $this->json($student);
        }

        abort(403);
    }
}
