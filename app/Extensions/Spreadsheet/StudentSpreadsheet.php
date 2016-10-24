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
                    $name = $this->getNameParts($col[2]);

                    $contents[] = [
                        'id'            => parseStudentId($col[1]),
                        'last_name'     => $name['last_name'],
                        'middle_name'   => $name['first_name'],
                        'first_name'    => $name['middle_name'],
                        'course'        => $col[3],
                        'section'       => $col[4]
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

    protected function getNameParts($name)
    {
        $parts = [
            'first_name'    => null,
            'middle_name'   => null,
            'last_name'     => null
        ];

        $nameComma = explode(',', $name);
        $nameSpaces = explode(' ', $nameComma[1]);

        $parts['last_name'] = trim($nameComma[0]);
        $parts['middle_name'] = trim(array_pop($nameSpaces));
        $parts['first_name'] = trim(implode(' ', $nameSpaces));

        return $parts;
    }
}
