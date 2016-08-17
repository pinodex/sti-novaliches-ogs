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
use App\Models\Grade;
use App\Models\Faculty;

class GradeSpreadsheet extends AbstractSpreadsheet
{
    const SETTINGS_SHEET_NUMBER_LECTURE = 0;

    const SUMMARY_SHEET_NUMBER_LECTURE = 6;

    const SETTINGS_SHEET_NUMBER_LAB = 0;

    const SUMMARY_SHEET_NUMBER_LAB = 7;

    const MODE_LECTURE = 0;

    const MODE_LAB = 1;

    protected $mode, $settingsSheetNumber, $summarySheetNumber;

    public function __construct($filePath)
    {
        parent::__construct($filePath);

        // Use predefined sheet indices by default
        if (count($this->getSheets()) == 10) {
            $this->mode = self::MODE_LAB;

            $this->settingsSheetNumber = self::SETTINGS_SHEET_NUMBER_LAB;
            $this->summarySheetNumber = self::SUMMARY_SHEET_NUMBER_LAB;
        }

        if (count($this->getSheets()) == 9) {
            $this->mode = self::MODE_LECTURE;

            $this->settingsSheetNumber = self::SETTINGS_SHEET_NUMBER_LECTURE;
            $this->summarySheetNumber = self::SUMMARY_SHEET_NUMBER_LECTURE;
        }

        // Or if we can determine sheet indices JIT, use it.
        if ($settingsSheetNumber = $this->getSheetIndexByName('SETTINGS')) {
            $this->settingsSheetNumber = $settingsSheetNumber;
        }

        if ($summarySheetNumber = $this->getSheetIndexByName('Summary')) {
            $this->summarySheetNumber = $summarySheetNumber;
        }
    }

    public function isValid()
    {
        $initialCheckOk =
            $this->mode !== null &&
            $this->spreadsheet !== null &&
            $this->getSheetIndexByName('SETTINGS') !== false &&
            $this->getSheetIndexByName('Summary') !== false;

        return $initialCheckOk;
    }

    public function getParsedContents()
    {
        $contents = [
            'metadata' => [],
            'students' => []
        ];

        $this->changeSheet($this->settingsSheetNumber);

        foreach ($this->spreadsheet as $row => $col) {
            if ($row == 1) {
                $contents['metadata']['subject'] = $col[10];
            }

            if ($row == 2) {
                $contents['metadata']['section'] = $col[10];
            }
        }

        $this->changeSheet($this->summarySheetNumber);

        foreach ($this->spreadsheet as $row => $col) {
            if ($row <= 6) {
                continue;
            }

            if ($row == 7) {
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
                'name'              => $col[4],

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

        return $contents;
    }

    public function importToDatabase()
    {
        $importerId = null;

        if (func_num_args() > 0) {
            $importer = func_get_arg(0);

            if ($importer instanceof Faculty) {
                $importerId = $importer->id;
            }
        }

        $contents = $this->getParsedContents();
        $tableName = with(new Grade)->getTable();
        
        $values = [];
        $bindings = [];

        $tables = '(student_id,importer_id,subject,section,prelim_grade,midterm_grade,prefinal_grade,'.
            'final_grade,prelim_presences,midterm_presences,prefinal_presences,final_presences,prelim_absences,'.
            'midterm_absences,prefinal_absences,final_absences)';

        foreach ($contents['students'] as $student) {
            $values[] = '(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

            $bindings = array_merge($bindings, [
                $student['student_id'],
                $importerId,
                $contents['metadata']['subject'],
                $contents['metadata']['section'],
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
