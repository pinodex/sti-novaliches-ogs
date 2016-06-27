<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions\Importer;

/**
 * Grading Sheet importer interface
 */
interface ImporterInterface
{
    /**
     * Import sheets
     * 
     * @param array $sheets Sheets to import
     */
    public static function import($sheets);
}
