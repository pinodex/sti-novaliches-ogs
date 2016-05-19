<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Exceptions;

/**
* Thrown when an auth error occurs
*/
class AuthException extends \Exception
{
    const USER_NOT_FOUND = 1;

    const INVALID_PASSWORD = 2;

    const ACCOUNT_LOCKED = 3;

    /**
     * Constructs AuthException
     * 
     * @param string $message Exception message
     * @param int $code Exception code
     */
    function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}