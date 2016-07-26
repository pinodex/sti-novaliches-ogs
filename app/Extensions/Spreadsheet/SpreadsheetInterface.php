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

interface SpreadsheetInterface
{
    /**
     * Get sheets
     * 
     * @return array
     */
    public function getSheets();

    /**
     * Get sheet index by name
     * 
     * @param string $name Sheet name to search
     * @param boolean $caseInsensitive Case-insensitive search
     * 
     * @return int
     */
    public function getSheetIndexByName($search, $caseInsensitive = false);

    /**
     * Validates the spreadsheet
     * 
     * @return boolean If spreadsheet is valid
     */
    public function isValid();

    /**
     * Get parsed contents from spreadsheet file
     * 
     * @return array
     */
    public function getParsedContents();
}