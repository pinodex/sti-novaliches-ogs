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

use DB;
use App\Models\Omega;

class OmegaSpreadsheet extends AbstractSpreadsheet
{
    public function isValid()
    {
        $columns = [
            'school_year',
            'semester',
            'course_code',
            'year_level',
            'year_level_sem',
            'subject_code',
            'section',
            'student_no',
            'fpercent_grade',
            'fpoint_grade',
            'status',
            'remarks',
            'midterm_grade',
            'mpercent_grade',
            'prelim_grade',
            'ppercent_grade',
            'user_created',
            'date_created',
            'user_updated',
            'date_updated',
            'pfinal_grade',
            'pfpercent_grade',
            'branch_code',
            'ffinal_grade',
            'ffpercent_grade',
            'compl_grade',
            'credits',
            'fcompl_grade',
            'credit_group'
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
                    'student_id'        => $this->parseStudentId($col[7]),
                    'subject'           => strtoupper(cleanString($col[5])),
                    'section'           => strtoupper(cleanString($col[6])),
                    'prelim_grade'      => parseGrade($col[14]),
                    'midterm_grade'     => parseGrade($col[12]),
                    'prefinal_grade'    => parseGrade($col[20]),
                    'final_grade'       => parseGrade($col[23]),
                    'actual_grade'      => parseGrade($col[9])
                ];
            }
        }

        return $contents;
    }

    public function importToDatabase()
    {
        $tableName = with(new Omega)->getTable();
        $chunks = array_chunk($this->getParsedContents(), 1000);

        $tables = '(student_id,subject,section,prelim_grade,midterm_grade,prefinal_grade,final_grade,actual_grade)';

        DB::beginTransaction();

        foreach ($chunks as $chunk) {
            $values = [];
            $bindings = [];

            foreach ($chunk as $grade) {
                $values[] = '(?,?,?,?,?,?,?,?)';
                $bindings = array_merge($bindings, array_values($grade));
            }
            
            $values = implode(',', $values);
            $query = "INSERT INTO {$tableName} {$tables} VALUES {$values} ON DUPLICATE KEY UPDATE " .
                'prelim_grade = VALUES(prelim_grade),' .
                'midterm_grade = VALUES(midterm_grade),' .
                'prefinal_grade = VALUES(prefinal_grade),' .
                'final_grade = VALUES(final_grade),' .
                'actual_grade = VALUES(actual_grade)';

            DB::insert($query, $bindings);
        }

        DB::commit();
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
