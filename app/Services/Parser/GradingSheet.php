<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Services\Parser;

use App\Services\Parser;
use App\Services\Helper;

/**
 * Grading Sheet parser
 */
class GradingSheet extends Parser
{
    public function getSheetContents($index)
    {
        $output = array(
            'metadata' => array(),
            'students' => array()
        );

        $this->changeSheet($index);

        foreach ($this->spreadsheet as $i => $row) {
            if ($i == 3) {
                $output['metadata']['section'] = $row[3];
            }

            if ($i == 6) {
                $output['metadata']['subject'] = explode(' ', $row[0])[0];
            }

            if ($i == 9) {
                $output['metadata']['prelim_attendance_hours'] = $row[186];
                $output['metadata']['midterm_attendance_hours'] = $row[202];
                $output['metadata']['prefinal_attendance_hours'] = $row[218];
                $output['metadata']['final_attendance_hours'] = $row[234];
            }

            if ($i >= 10 && Helper::isStudentId($row[2])) {
                $output['students'][] = array(
                    'student_id'            => $row[2],
                    'name'                  => $row[4],
                    'prelim_grade'          => $row[6],
                    'midterm_grade'         => $row[7],
                    'prefinal_grade'        => $row[8],
                    'final_grade'           => $row[9],
                    'prelim_absent_hours'   => $row[186],
                    'midterm_absent_hours'  => $row[202],
                    'prefinal_absent_hours' => $row[218],
                    'final_absent_hours'    => $row[234],
                );
            }
        }
        
        return $output;
    }
}
