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
        $columns = [
            "course_code",
            "year_level",
            "year_level_sem",
            "fpercent_grade",
            "fpoint_grade",
            "status",
            "last_name",
            "first_name",
            "middle_name",
            "user_created",
            "date_created",
            "user_updated",
            "date_updated",
            "semester",
            "course_code",
            "year_level",
            "year_level_sem",
            "subject_code",
            "section",
            "student_no",
            "remarks",
            "midterm_grade",
            "mpercent_grade",
            "prelim_grade",
            "ppercent_grade",
            "professor_code",
            "time1",
            "time2",
            "professor_name",
            "school_year",
            "pfinal_grade",
            "pfpercent_grade",
            "ffinal_grade",
            "ffpercent_grade",
            "compl_grade",
            "fcompl_grade",
            "branch_code",
            "subject_name",
            "credits",
            "credit_group",
            "pass"
        ];

        foreach ($this->spreadsheet->getSheetIterator() as $sheet) {
            if ($sheet->getIndex() != 0) {
                break;
            }

            foreach ($sheet->getRowIterator() as $row => $col) {
                return array_intersect($col, $columns) == $columns;
            }
        }
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
                    'subject'           => strtoupper(cleanString($col[17])),
                    'section'           => strtoupper(cleanString($col[18])),
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
