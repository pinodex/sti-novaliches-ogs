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

        foreach (['prelim', 'midterm', 'prefinal', 'final'] as $term) {
            $sheetIndex = $this->getSheetIndexByName($term, true);

            if ($sheetIndex === false) {
                continue;
            }

            $this->changeSheet($sheetIndex);

            foreach ($this->spreadsheet as $i => $row) {
                if ($i > 0 && isStudentId($row[1])) {
                    $studentId = parseStudentId($row[1]);

                    if (!array_key_exists($studentId, $contents)) {
                        $contents[$studentId] = [
                            'prelim'    => false,
                            'midterm'   => false,
                            'prefinal'  => false,
                            'final'     => false
                        ];
                    }

                    $contents[$studentId][$term] = true;
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

            $query = 'INSERT INTO ' . $tableName . ' ' . $tables . ' VALUES' . implode(',', $values) . ' ' .
                'ON DUPLICATE KEY UPDATE prelim = VALUES(prelim), midterm = VALUES(midterm), ' .
                'prefinal = VALUES(prefinal), final = VALUES(final)';

            DB::insert($query, $bindings);
        }
    }
}
