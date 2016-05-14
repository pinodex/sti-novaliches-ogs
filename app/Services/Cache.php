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

use App\Components\Cache as BasicCache;

/**
 * Provides static class for CSRF from CSRF service
 */
class Cache extends Service
{
    /**
     * Get new instance of \App\Components\Cache component
     * 
     * @return \App\Components\Cache
     */
    public static function newInstance()
    {
        return new BasicCache(self::$app['application.cache_dir'], self::$app['session']->getId());
    }
}
