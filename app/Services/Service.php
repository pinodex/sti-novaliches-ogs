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

use Silex\Application;

/**
 * Base service class
 * 
 * It also holds an instance of Application
 */
class Service
{
    /**
     * @var Silex\Application The application instance
     */
    protected static $app;

    /**
     * Set application container
     *
     * @param Silex\Application $app Application
     */
    public static function setApplication(Application $app)
    {
        self::$app = $app;
    }
}
