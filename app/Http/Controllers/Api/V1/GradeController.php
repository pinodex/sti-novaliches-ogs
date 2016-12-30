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
use App\Models\Grade;

class GradeController extends Controller
{
    /**
     * Show record
     * 
     * @param int $id Student ID
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        $student = Student::find($id);

        if (!$student->canBeViewedBy($user)) {
            abort(403);
        }

        $output = [];

        $student->grades->each(function (Grade $grade) use (&$output) {
            $output[] = [
                'subject' => $grade->subject,
                'section' => $grade->section,
                'records' => [
                    'prelim' => [
                        'grade'         => $grade->getOriginal('prelim_grade'),
                        'class_hours'   => (double) $grade->getOriginal('prelim_presences'),
                        'hours_absent'  => (double) $grade->getOriginal('prelim_absences')
                    ],

                    'midterm' => [
                        'grade'         => $grade->getOriginal('midterm_grade'),
                        'class_hours'   => (double) $grade->getOriginal('midterm_presences'),
                        'hours_absent'  => (double) $grade->getOriginal('midterm_absences')
                    ],

                    'prefinal' => [
                        'grade'         => $grade->getOriginal('prefinal_grade'),
                        'class_hours'   => (double)$grade->getOriginal('prefinal_presences'),
                        'hours_absent'  => (double)$grade->getOriginal('prefinal_absences')
                    ],

                    'final' => [
                        'grade'         => $grade->getOriginal('final_grade'),
                        'class_hours'   => (double)$grade->getOriginal('final_presences'),
                        'hours_absent'  => (double)$grade->getOriginal('final_absences')
                    ]
                ]
            ];
        });

        return $this->json($output);
    }
}
