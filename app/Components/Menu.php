<?php

/*
 * This file is part of the SIS for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components;

use Auth;

class Menu
{
    /**
     * Pre-process menu items
     * 
     * @param array $items Menu items
     * 
     * @return array
     */
    public static function process(array $items)
    {
        $output = [];

        foreach ($items as $item) {
            if (array_key_exists('visibility', $item) &&
                !static::isVisible($item['visibility'])) {

                continue;
            }

            $output[] = [
                'header'    => $item['header'],
                'list'      => static::processList($item['list'])
            ];
        }

        return $output;
    }

    protected static function processList(array $list)
    {
        $output = [];

        foreach ($list as $item) {
            if (array_key_exists('visibility', $item) &&
                !static::isVisible($item['visibility'])) {

                continue;
            }

            unset($item['visibility']);
            $output[] = $item;
        }

        return $output;
    }

    /**
     * Check if an entry is visible to user
     * 
     * @param mixed $visibility Acl array or closure
     * 
     * @return boolean
     */
    protected static function isVisible($visibility)
    {
        if (!Auth::check()) {
            return false;
        }

        if (is_array($visibility)) {
            return Auth::user()->canDo($visibility);
        }

        if (is_callable($visibility)) {
            return $visibility();
        }

        return false;
    }
}
