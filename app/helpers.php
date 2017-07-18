<?php

/*
 * This file is part of the SIS for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!function_exists('default_avatar_thumb')) {
    function default_avatar_thumb()
    {
        return '/assets/img/default-thumb.jpg';
    }
}

if (!function_exists('default_avatar_picture')) {
    function default_avatar_picture()
    {
        return '/assets/img/default-image.jpg';
    }
}

if (!function_exists('now')) {
    function now()
    {
        return date('Y-m-d H:i:s');
    }
}
