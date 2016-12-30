<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions;

use Session;

class Alert
{
    const PRIMARY = 'primary';

    const SUCCESS = 'success';

    const WARNING = 'warning';

    const DANGER = 'danger';

    const INFO = 'info';

    /**
     * @var string Session key to use in flash messages
     */
    protected static $key = 'alerts';

    /**
     * Make alert message
     *
     * @param string $message Message content
     * @param string $type Message type
     */
    public static function make($message, $type)
    {
        $currentBag = [];

        if (Session::has(static::$key)) {
            $currentBag = Session::get(static::$key);
        }

        $currentBag[] = [$type, $message];

        Session::flash(static::$key, $currentBag);
    }

    public static function primary($message)
    {
        static::make($message, static::PRIMARY);
    }

    public static function success($message)
    {
        static::make($message, static::SUCCESS);
    }

    public static function warning($message)
    {
        static::make($message, static::WARNING);
    }

    public static function danger($message)
    {
        static::make($message, static::DANGER);
    }

    public static function info($message)
    {
        static::make($message, static::INFO);
    }
}
