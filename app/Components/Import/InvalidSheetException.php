<?php

/*
 * This file is part of the SIS for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\Import;

use Exception;

class InvalidSheetException extends Exception
{
    public function __construct($message = 'The uploaded sheet is invalid')
    {
        parent::__construct($message);
    }
}
