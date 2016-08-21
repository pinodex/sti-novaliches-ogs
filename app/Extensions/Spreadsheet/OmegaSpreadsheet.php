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

class OmegaSpreadsheet extends AbstractSpreadsheet
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

            foreach ($sheet->getRowIterator() as $row => $col) {
                if ($row <= 1) {
                    continue;
                }

                $contents[] = [
                    'student_id'        => $this->parseStudentId($col[19]),
                    'name'              => $col[6] . ', ' . $col[7] . ' ' . $col[8],
                    'subject'           => $col[17],
                    'section'           => $col[18],
                    'prelim_grade'      => parseGrade($col[23]),
                    'midterm_grade'     => parseGrade($col[21]),
                    'prefinal_grade'    => parseGrade($col[30]),
                    'final_grade'       => parseGrade($col[32])
                ];
            }
        }

        return $contents;
    }

    public function importToDatabase()
    {
    }

    /**
     * Method to parse student ID.
     * 
     * @param mixed $id Student ID
     * 
     * @return string
     */
    protected function parseStudentId($id)
    {
        if (!is_string($id)) {
            $id = strval($id);
        }

        $id = str_replace('-', '', $id);

        // 11 is the length of our student IDs.
        return str_pad($id, 11, '0', STR_PAD_LEFT);
    }
}
