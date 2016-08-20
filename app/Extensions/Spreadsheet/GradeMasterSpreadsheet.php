<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions\Spreadsheet;

use App\Models\Grade;
use App\Models\Faculty;

class GradeMasterSpreadsheet extends AbstractSpreadsheet
{
    public function isValid()
    {
        return true;
    }

    public function getParsedContents()
    {
        $contents = [];

        foreach ($this->spreadsheet->getSheetIterator() as $sheet) {            
            foreach ($sheet->getRowIterator() as $row => $col) {
                if ($row < 2) {
                    continue;
                }

                $contents[] = [
                    'student_id'        => $this->fixStudentId($col[8]),
                    'subject'           => $col[6],
                    'section'           => $col[7],
                    'prelim_grade'      => parseGrade($col[15]),
                    'midterm_grade'     => parseGrade($col[13]),
                    'prefinal_grade'    => parseGrade($col[21]),
                    'final_grade'       => parseGrade($col[24])
                ];
            }
        }
        
        return $contents;
    }

    /**
     * Method to fix student ID formatting. The spreadsheet stores it as
     * a floating point. Leading zeroes are discarded. This method will
     * correct that sucker.
     * 
     * @param mixed $id Student ID
     * 
     * @return string
     */
    private function fixStudentId($id)
    {
        if (is_numeric($id)) {
            if (!is_integer($id)) {
                $id = intval($id);
            }

            $id = strval($id);
        }

        // 11 is the length of our student IDs.
        return str_pad($id, 11, '0', STR_PAD_LEFT);
    }
}
