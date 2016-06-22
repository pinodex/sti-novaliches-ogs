<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    'student' => App\Extensions\User\Roles\StudentUserRole::class,
    'admin' => App\Extensions\User\Roles\AdminUserRole::class,
    'head' => App\Extensions\User\Roles\HeadUserRole::class
];
