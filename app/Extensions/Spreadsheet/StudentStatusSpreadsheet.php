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

        foreach ($this->spreadsheet->getSheetIterator() as $sheet) {
            if ($sheet->getIndex() != 0) {
                break;
            }

            foreach ($sheet->getRowIterator() as $row => $col) {
                if ($row > 0 && isStudentId($col[1])) {
                    $contents[] = parseStudentId($col[1]);
                }
            }
        }

        return $contents;
    }

    public function importToDatabase()
    {
        if (func_num_args() == 0) {
            return;
        }

        $period = func_get_arg(0);

        $tableName = with(new StudentStatus)->getTable();
        $chunks = array_chunk($this->getParsedContents(), 500);

        foreach ($chunks as $students) {
            $values = [];
            $bindings = [];

            $tables = "(student_id,{$period})";

            foreach ($students as $student) {
                $values[] = '(?, true)';
            }

            $values = implode(',', $values);
            $query = "INSERT INTO {$tableName} {$tables} VALUES {$values} ON DUPLICATE KEY UPDATE {$period} = VALUES({$period});";

            DB::insert($query, $students);
        }
    }
}
