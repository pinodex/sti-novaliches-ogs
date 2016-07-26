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

use Serializable;
use SpreadsheetReader;
use Illuminate\Support\Collection;
use App\Jobs\ImportToDatabaseJob;

/**
 * Wraps around nuovo/spreadsheet-reader
 */
abstract class AbstractSpreadsheet implements Serializable, SpreadsheetInterface
{
    /**
     * @var SpreadsheetReader Spreadsheet reader spreadsheet
     */
    protected $spreadsheet;

    /**
     * @var string Path to spreadsheet file
     */
    protected $filePath;

    /**
     * @param string $filePath Excel file path
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;

        try {
            $this->spreadsheet = new SpreadsheetReader($filePath);
        } catch (\Exception $e) {
            // Exception ignored. $this->spreadsheet will be null.
        }
    }

    public function getSheets()
    {
        return $this->spreadsheet->Sheets();
    }

    public function getSheetIndexByName($search, $caseInsensitive = false)
    {
        if ($caseInsensitive) {
            return array_search(strtolower($search), array_map('strtolower', $this->getSheets()));
        }

        return array_search($search, $this->getSheets());
    }

    public function isValid()
    {
        return $this->spreadsheet !== null;
    }

    /**
     * Change active sheet
     * 
     * @param int $index Sheet index
     */
    public function changeSheet($index)
    {
        return $this->spreadsheet->ChangeSheet($index);
    }

    /**
     * Import spreadsheet contents to database
     * 
     * @return boolean True if import was successful.
     */
    public function importToDatabase() {}

    /**
     * Create queue job for importing to database
     * 
     * @return \App\Jobs\Jobs
     */
    public function createImportToDatabaseJob()
    {
        return new ImportToDatabaseJob($this, func_get_args());
    }

    public function serialize()
    {
        // Why? because we can't serialize SimpleXMLElement, so we only serialize
        // the file path. It will be then reconstructed when unserialized.
        // 
        // See AbstractSpreadsheet->unserialize();
        return $this->filePath;
    }

    public function unserialize($filePath)
    {
        $this->__construct($filePath);
    }
}
