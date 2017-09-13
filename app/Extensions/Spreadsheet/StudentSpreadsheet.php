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
use App\Models\Student;

class StudentSpreadsheet extends AbstractSpreadsheet
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
                if ($row > 0 && isStudentId($col[1])) {
                    $contents[] = [
                        'id'            => parseStudentId($col[1]),
                        'last_name'     => $col[2],
                        'first_name'    => $col[3],
                        'middle_name'   => $col[4],
                        'course'        => $col[5],
                        'section'       => $col[6]
                    ];
                }
            }
        }

        return $contents;
    }

    public function importToDatabase()
    {
        $tableName = with(new Student)->getTable();
        $timestamp = date('Y-m-d H:i:s');
        $chunks = array_chunk($this->getParsedContents(), 500);

        DB::beginTransaction();

        foreach ($chunks as $students) {
            $values = [];
            $bindings = [];

            $tables = '(id, last_name, first_name, middle_name, course, section, created_at, updated_at)';

            foreach ($students as $i => $student) {
                $values[] = '(?, ?, ?, ?, ?, ?, "' . $timestamp . '", "' . $timestamp . '")';
                $bindings = array_merge($bindings, array_values($student));
            }

            $values = implode(',', $values);

            DB::insert("INSERT IGNORE INTO {$tableName} {$tables} VALUES {$values}", $bindings);
        }

        DB::commit();
    }
}
