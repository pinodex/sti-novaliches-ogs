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
class FacultySheet
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

    /**
     * Get sheet contents
     * 
     * @param int $index Sheet index
     * 
     * @return array
     */
    public function getSheetContents($index)
    {
        $output = array();
        $subject = '';
        $section = '';

        $this->excel->ChangeSheet($index);

        foreach ($this->excel as $i => $row) {
            if ($i >= 4) {
                if (empty($row[3]) &&
                    empty($row[4]) &&
                    empty($row[5]) &&
                    empty($row[8])) {

                    continue;
                }

                $output[] = array(
                    'last_name' => $row[3],
                    'first_name' => $row[4],
                    'middle_name' => $row[5],
                    'department' => $row[8]
                );
            }
        }
        
        return $output;
    }

    /**
     * Get sheets contents.
     * 
     * Don't be confused with getSheetContents. This method
     * accepts array of sheet indices as a parameter and return an
     * array of sheet contents.
     * 
     * Element keys will be the sheet name and values will be the contents.
     * 
     * This method calls $this->getSheetContents() internally.
     * 
     * @param array $indices Sheets indices
     * 
     * @return array
     */
    public function getSheetsContents($indices)
    {
        $output = array();

        foreach ($indices as $index) {
            $output[$this->sheets[$index]] = $this->getSheetContents($index);
        }

        return $output;
    }
}
