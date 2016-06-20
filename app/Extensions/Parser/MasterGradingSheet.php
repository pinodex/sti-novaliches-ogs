<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions\Parser;

use App\Extensions\Parser;
use App\Services\Helper;

/**
 * Master Grading Sheet parser
 */
class MasterGradingSheet extends Parser
{
    public function getSheetContents($index)
    {
        $output = array();
        $this->changeSheet($index);

        foreach ($this->spreadsheet as $i => $row) {
            if ($i < 1) {
                continue;
            }

            if (strlen($row[8]) == 10) {
                // Left pad with 1 zero.
                $row[8] = '0' . $row[8];
            }

            $row[14] = Helper::parseGrade($row[14]);
            $row[22] = Helper::parseGrade($row[22]);
            $row[16] = Helper::parseGrade($row[16]);
            $row[25] = Helper::parseGrade($row[25]);

            $output[] = array(
                'student_id'        => $row[8],
                'subject'           => $row[6],
                'section'           => $row[7],
                'prelim_grade'      => $row[16],
                'midterm_grade'     => $row[14],
                'prefinal_grade'    => $row[22],
                'final_grade'       => $row[25]
            );
        }
        
        return $output;
    }
}
