<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\Parser;

use App\Components\Parser;

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
                    'last_name' => trim($row[3]),
                    'first_name' => trim($row[4]),
                    'middle_name' => trim($row[5]),
                    'department' => trim($row[8])
                );
            }
        }
        
        return $output;
    }
}
