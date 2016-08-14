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

        foreach ($this->spreadsheet as $i => $row) {
            if ($i > 0 && isStudentId($row[1])) {
                $name = $this->getNameParts($row[2]);

                $contents[] = [
                    'id'            => parseStudentId($row[1]),
                    'last_name'     => $name['last_name'],
                    'middle_name'   => $name['first_name'],
                    'first_name'    => $name['middle_name'],
                    'course'        => $row[3],
                    'section'       => $row[4]
                ];
            }
        }

        return $contents;
    }

    public function importToDatabase()
    {
        $tableName = with(new Student)->getTable();
        $timestamp = date('Y-m-d H:i:s');
        $chunks = array_chunk($this->getParsedContents(), 500);

        foreach ($chunks as $students) {
            $values = [];
            $bindings = [];

            $tables = '(id, last_name, first_name, middle_name, course, section, created_at, updated_at)';

            foreach ($students as $i => $student) {
                $values[] = '(?, ?, ?, ?, ?, ?, "' . $timestamp . '", "' . $timestamp . '")';
                $bindings = array_merge($bindings, array_values($student));
            }

            DB::insert('INSERT IGNORE INTO ' . $tableName . ' ' . $tables . ' VALUES ' . implode(',', $values), $bindings);
        }
    }

    protected function getNameParts($name)
    {
        $parts = [
            'first_name'    => null,
            'middle_name'   => null,
            'last_name'     => null
        ];

        $nameComma = explode(',', $name);
        $nameSpaces = explode(' ', $nameComma[1]);

        $parts['last_name'] = $nameComma[0];
        $parts['middle_name'] = array_pop($nameSpaces);
        $parts['first_name'] = implode(' ', $nameSpaces);

        return $parts;
    }
}
