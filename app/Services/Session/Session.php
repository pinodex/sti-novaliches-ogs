<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Services\Session;

use App\Services\Service;

/**
 * Static class wrapper for session service
 */
class Session extends Service
{
    public static function __callStatic($name, $args)
    {
        return call_user_func_array([self::$app['session'], $name], $args);
    }
}
