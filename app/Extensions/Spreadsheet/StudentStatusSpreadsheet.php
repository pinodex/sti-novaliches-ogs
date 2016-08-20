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
use App\Models\StudentStatus;

class StudentStatusSpreadsheet extends AbstractSpreadsheet
{
    public function isValid()
    {
        return true;
    }

    public function getParsedContents()
    {
        $contents = [];
        $periods = ['prelim', 'midterm', 'prefinal', 'final'];

        foreach ($this->spreadsheet->getSheetIterator() as $sheet) {
            $period = strtolower($sheet->getName());

            if (!in_array($period, $periods)) {
                continue;
            }

            foreach ($sheet->getRowIterator() as $row => $col) {
                if ($row > 0 && isStudentId($col[1])) {
                    $studentId = parseStudentId($col[1]);

                    if (!array_key_exists($studentId, $contents)) {
                        $contents[$studentId] = [
                            'prelim'    => false,
                            'midterm'   => false,
                            'prefinal'  => false,
                            'final'     => false
                        ];
                    }

                    $contents[$studentId][$period] = true;
                }
            }
        }

        // flatten
        foreach ($contents as $id => $status) {
            $contents[] = array_merge(['student_id' => $id], $status);

            unset($contents[$id]);
        }

        return $contents;
    }

    public function importToDatabase()
    {
        $tableName = with(new StudentStatus)->getTable();
        $chunks = array_chunk($this->getParsedContents(), 500);

        foreach ($chunks as $students) {
            $values = [];
            $bindings = [];

            $tables = '(student_id,prelim,midterm,prefinal,final)';

            foreach ($students as $student) {
                $values[] = '(?,?,?,?,?)';
                $bindings = array_merge($bindings, array_values($student));
            }

            $values = implode(',', $values);

            $query = "INSERT INTO {$tableName} {$tables} VALUES {$values} ON DUPLICATE KEY UPDATE " .
                'prelim = VALUES(prelim),' .
                'midterm = VALUES(midterm),' .
                'prefinal = VALUES(prefinal),' .
                'final = VALUES(final)';

            DB::insert($query, $bindings);
        }
    }
}
