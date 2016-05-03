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
 * Student Sheet parser
 */
class StudentSheet extends Parser
{
    public function getSheetContents($index)
    {
        $output = array();
        $this->changeSheet($index);

        foreach ($this->spreadsheet as $i => $row) {
            if ($i > 0 && Helper::isStudentId($row[0])) {
                $output[] = array(
                    'id'            => $row[0],
                    'last_name'     => $row[1],
                    'first_name'    => $row[2],
                    'middle_name'   => $row[3],
                    'course'        => $row[4]
                );
            }
        }
        
        return $output;
    }
}
