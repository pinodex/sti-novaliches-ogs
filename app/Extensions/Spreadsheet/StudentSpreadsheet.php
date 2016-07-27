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
use App\Models\Faculty;
use App\Models\Department;

class StudentSpreadsheet extends AbstractSpreadsheet
{
    public function isValid()
    {
        return true;
    }

    public function getParsedContents()
    {
        $contents = [];

        foreach ($this->spreadsheet as $i => $row) {
            if ($i > 0 && isStudentId($row[0])) {
                $contents[] = [
                    'id'            => $row[0],
                    'last_name'     => $row[1],
                    'first_name'    => $row[2],
                    'middle_name'   => $row[3],
                    'course'        => $row[4]
                ];
            }
        }

        return $contents;
    }

    public function importToDatabase()
    {
        $timestamp = date('Y-m-d H:i:s');
        $chunks = array_chunk($this->getParsedContents(), 500);

        foreach ($chunks as $students) {
            $values = [];
            $bindings = [];

            $tables = '(id, last_name, first_name, middle_name, course, created_at, updated_at)';

            foreach ($students as $i => $student) {
                $values[] = '(?, ?, ?, ?, ?, "' . $timestamp . '", "' . $timestamp . '")';
                $bindings = array_merge($bindings, array_values($student));
            }

            DB::insert('insert ignore into students ' . $tables . ' values ' . implode(',', $values), $bindings);
        }
    }
}
