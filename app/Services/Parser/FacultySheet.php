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
 * Faculty Sheet parser
 */
class FacultySheet extends Parser
{
    public function getSheetContents($index)
    {
        $output = array();
        $this->changeSheet($index);

        foreach ($this->spreadsheet as $i => $row) {
            if ($i >= 4) {
                if (empty($row[3]) &&
                    empty($row[4]) &&
                    empty($row[5]) &&
                    empty($row[8])) {

                    continue;
                }

                $output[] = array(
                    'last_name' => $row[3],
                    'first_name' => $row[4],
                    'middle_name' => $row[5],
                    'department' => $row[8]
                );
            }
        }
        
        return $output;
    }
}
