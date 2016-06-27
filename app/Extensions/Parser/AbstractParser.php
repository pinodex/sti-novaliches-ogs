<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions\Parser;

use SpreadsheetReader;

/**
 * Base class for spreadsheet parsers.
 */
abstract class AbstractParser
{
    /**
     * @var SpreadsheetReader
     */
    protected $spreadsheet;

    /**
     * @var array Sheets
     */
    protected $sheets = [];

    /**
     * @param string $filePath Excel file path
     */
    public function __construct(SpreadsheetReader $spreadsheet)
    {
        $this->spreadsheet = $spreadsheet;
        $this->sheets = $spreadsheet->Sheets();
    }

    /**
     * SpreadsheetReader parser factory
     * 
     * @param string $filePath Spreadsheet file path
     * 
     * @return Parser
     */
    public static function parse($filePath)
    {
        return new static(new SpreadsheetReader($filePath));
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
     * Change active sheet
     * 
     * @param int $index Sheet index
     */
    public function changeSheet($index)
    {
        $this->spreadsheet->ChangeSheet($index);
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
        return [];
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
    public function getSheetsContent($indices)
    {
        $output = [];

        foreach ($indices as $index) {
            $output[$this->sheets[$index]] = $this->getSheetContents($index);
        }

        return $output;
    }
}
