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
            if ($sheet->getIndex() != 0) {
                break;
            }
            
            foreach ($sheet->getRowIterator() as $i => $row) {
                if ($i < 2) {
                    continue;
                }

                $contents[] = [
                    'student_id'        => $this->fixStudentId($row[8]),
                    'subject'           => $row[6],
                    'section'           => $row[7],
                    'prelim_grade'      => parseGrade($row[15]),
                    'midterm_grade'     => parseGrade($row[13]),
                    'prefinal_grade'    => parseGrade($row[21]),
                    'final_grade'       => parseGrade($row[24])
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
