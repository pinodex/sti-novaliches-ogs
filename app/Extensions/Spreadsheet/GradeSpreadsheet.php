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
use App\Models\Grade;
use App\Models\Faculty;
use Illuminate\Support\Collection;

class GradeSpreadsheet extends AbstractSpreadsheet
{
    public function isValid()
    {
        $hasSettings = false;
        $hasSummary = false;

        $subject = '';
        $section = '';

        foreach ($this->spreadsheet->getSheetIterator() as $sheet) {
            if (strtolower($sheet->getName()) == 'settings') {
                $hasSettings = true;

                foreach ($sheet->getRowIterator() as $row => $col) {
                    if ($row == 2) {
                        $subject = $col[10];
                    }

                    if ($row == 3) {
                        $section = $col[10];
                    }
                }
            }

            if (strtolower($sheet->getName()) == 'summary') {
                $hasSummary = true;
            }
        }

        if (empty(trim($subject)) || empty(trim($section))) {
            return false;
        }

        return $hasSettings && $hasSummary;
    }

    public function getParsedContents()
    {
        $contents = [
            'metadata' => [],
            'students' => []
        ];

        foreach ($this->spreadsheet->getSheetIterator() as $sheet) {
            if (strtolower($sheet->getName()) == 'settings') {
                foreach ($sheet->getRowIterator() as $row => $col) {
                    if ($row == 2) {
                        $contents['metadata']['subject'] = strtoupper(cleanString($col[10]));
                    }

                    if ($row == 3) {
                        $contents['metadata']['sections'] = array_map(function ($string) {
                            return cleanString($string);
                        }, explode('/', strtoupper($col[10])));
                    }
                }
            }

            if (strtolower($sheet->getName()) == 'summary') {
                foreach ($sheet->getRowIterator() as $row => $col) {
                    if ($row <= 7) {
                        continue;
                    }

                    if ($row == 8) {
                        $contents['metadata']['prelim_presences'] = $this->parseHours($col[19]);
                        $contents['metadata']['midterm_presences'] = $this->parseHours($col[20]);
                        $contents['metadata']['prefinal_presences'] = $this->parseHours($col[21]);
                        $contents['metadata']['final_presences'] = $this->parseHours($col[22]);

                        continue;
                    }

                    $studentId = $this->parseStudentId($col[2]);

                    if (empty($col[2]) || !isStudentId($studentId)) {
                        continue;
                    }

                    $contents['students'][] = [
                        'student_id'        => $studentId,
                        'name'              => ucwords(strtolower($col[4])),

                        'prelim_grade'      => parseGrade($col[6]),
                        'midterm_grade'     => parseGrade($col[8]),
                        'prefinal_grade'    => parseGrade($col[10]),
                        'final_grade'       => parseGrade($col[12]),

                        'prelim_absences'   => $this->parseHours($col[19]),
                        'midterm_absences'  => $this->parseHours($col[20]),
                        'prefinal_absences' => $this->parseHours($col[21]),
                        'final_absences'    => $this->parseHours($col[22])
                    ];
                }
            }
        }

        return $contents;
    }

    public function importToDatabase()
    {
        $importerId = null;
        $filteredIds = Collection::make();

        if (func_get_arg(0) instanceof Faculty) {
            $importerId = func_get_arg(0)->id;
        }

        if (func_get_arg(1) instanceof Collection) {
            $filteredIds = func_get_arg(1);
        }

        $contents = $this->getParsedContents();
        $tableName = with(new Grade)->getTable();
        
        $values = [];
        $bindings = [];

        $tables = '(student_id,importer_id,subject,section,prelim_grade,midterm_grade,prefinal_grade,'.
            'final_grade,prelim_presences,midterm_presences,prefinal_presences,final_presences,prelim_absences,'.
            'midterm_absences,prefinal_absences,final_absences)';

        foreach ($contents['students'] as $student) {
            if ($filteredIds->contains($student['student_id'])) {
                continue;
            }

            $values[] = '(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

            $section = $contents['metadata']['sections'][0];

            $omega = Omega::select('section')
                ->where('student_id', $student['student_id'])
                ->where('subject', $contents['metadata']['subject'])
                ->whereIn('section', $contents['metadata']['sections'])
                ->first();

            if ($omega !== null) {
                $section = $omega->section;
            }

            $bindings = array_merge($bindings, [
                $student['student_id'],
                $importerId,
                $contents['metadata']['subject'],
                $section,
                $student['prelim_grade'],
                $student['midterm_grade'],
                $student['prefinal_grade'],
                $student['final_grade'],
                $contents['metadata']['prelim_presences'],
                $contents['metadata']['midterm_presences'],
                $contents['metadata']['prefinal_presences'],
                $contents['metadata']['final_presences'],
                $student['prelim_absences'],
                $student['midterm_absences'],
                $student['prefinal_absences'],
                $student['final_absences'],
            ]);
        }
        
        $values = implode(',', $values);

        if (empty($values)) {
            return;
        }

        // Probably not the best thing you would see today.
        $query = "INSERT INTO {$tableName} {$tables} VALUES {$values} ON DUPLICATE KEY UPDATE " .
                'importer_id = VALUES(importer_id),' .

                'prelim_grade = VALUES(prelim_grade),' .
                'midterm_grade = VALUES(midterm_grade),' .
                'prefinal_grade = VALUES(prefinal_grade), ' .
                'final_grade = VALUES(final_grade),' .

                'prelim_presences = VALUES(prelim_presences),' .
                'midterm_presences = VALUES(midterm_presences),' .
                'prefinal_presences = VALUES(prefinal_presences),' .
                'final_presences = VALUES(final_presences),' .

                'prelim_absences = VALUES(prelim_absences),' .
                'midterm_absences = VALUES(midterm_absences),' .
                'prefinal_absences = VALUES(midterm_absences),' .
                'final_absences = VALUES(final_absences)';
    
        DB::insert($query, $bindings);
    }

    /**
     * Method to parse student ID. The spreadsheet stores it as
     * a floating point. Leading zeroes are discarded. This method will
     * correct that sucker.
     * 
     * @param mixed $id Student ID
     * 
     * @return string
     */
    protected function parseStudentId($id)
    {
        if (is_numeric($id)) {
            if (!is_integer($id)) {
                $id = intval($id);
            }

            $id = strval($id);
        }

        $id = str_replace('-', '', $id);

        // 11 is the length of our student IDs.
        return str_pad($id, 11, '0', STR_PAD_LEFT);
    }

    /**
     * Parse hours to double
     * 
     * @param string $hour Hour
     * 
     * @return double
     */
    protected function parseHours($hour)
    {
        $hour = (double) $hour;

        if ($hour < 0) {
            $hour = 0.0;
        }

        return $hour;
    }
}
