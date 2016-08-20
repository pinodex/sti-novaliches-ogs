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

use Exception;
use Serializable;
use App\Jobs\ImportToDatabaseJob;
use Illuminate\Support\Collection;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;

/**
 * Wraps around Spout (box/spout)
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
    public function __construct($filePath, $format = null)
    {
        $this->filePath = $filePath;

        if ($format === null) {
            $format = pathinfo($filePath, PATHINFO_EXTENSION);
        }

        switch ($format) {
            case 'xlsx':
                $type = Type::XLSX;
                break;

            case 'csv':
                $type = Type::CSV;
                break;
        }

        if ($type !== null) {
            $this->spreadsheet = ReaderFactory::create($type);
            $this->spreadsheet->open($filePath);
        }
    }

    public function isValid()
    {
        return $this->spreadsheet !== null;
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
