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

use App\Models\Grade;
use App\Models\Faculty;

class GradeSpreadsheet extends AbstractSpreadsheet
{
    const SETTINGS_SHEET_NUMBER_LECTURE = 6;

    const SUMMARY_SHEET_NUMBER_LECTURE = 5;

    const SETTINGS_SHEET_NUMBER_LAB = 7;

    const SUMMARY_SHEET_NUMBER_LAB = 6;

    const MODE_LECTURE = 0;

    const MODE_LAB = 1;

    protected $mode, $settingsSheetNumber, $summarySheetNumber;

    public function __construct($filePath)
    {
        parent::__construct($filePath);

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
            if ($row == 2) {
                $contents['metadata']['subject'] = $col[16];
            }

            if ($row == 3) {
                $contents['metadata']['section'] = $col[16];
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

                'prelim_grade'      => parseGrade($col[5]),
                'midterm_grade'     => parseGrade($col[7]),
                'prefinal_grade'    => parseGrade($col[9]),
                'final_grade'       => parseGrade($col[11]),

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

        foreach ($contents['students'] as $item) {
            $grade = Grade::where([
                'student_id'    => $item['student_id'],
                'subject'       => $contents['metadata']['subject'],
                'section'       => $contents['metadata']['section']
            ])->first();

            if ($grade === null) {
                $grade = new Grade();
            }

            $grade->fill(array_merge($contents['metadata'], $item));
            $grade->importer_id = $importerId;

            $grade->save();
        }
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
