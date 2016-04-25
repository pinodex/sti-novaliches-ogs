<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Services;

use SpreadsheetReader;
use App\Services\Helper;

/**
 * Grading Sheet
 * 
 * Provides wrapper for nuovo/spreadsheet-reader
 */
class GradingSheet
{
    /**
     * @var SpreadsheetReader
     */
    private $excel;

    /**
     * @var array Sheets
     */
    private $sheets = array();

    /**
     * @param string $filePath Excel file path
     */
    public function __construct($filePath)
    {
        $this->excel = new SpreadsheetReader($filePath);
        $this->sheets = $this->excel->Sheets();
    }

    /**
     * Get sheets
     * 
     * @return array
     */
    public function getSheets()
    {
        return $this->sheets;
    }

    public function getSheetContents($index)
    {
        $output = array();
        $section = '';

        $this->excel->ChangeSheet($index);

        foreach ($this->excel as $i => $row) {
            if ($i == 3) {
                $section = $row[3];
            }

            if ($i >= 10 && Helper::isStudentId($row[2])) {
                $output[] = array(
                    'student_id' => $row[2],
                    'name'       => $row[4],
                    'section'    => $section,
                    'prelim'     => $row[6],
                    'midterm'    => $row[7],
                    'prefinal'   => $row[8],
                    'final'      => $row[9]
                );
            }
        }
        
        return $output;
    }
}
