<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Providers;

use App\Services\Hash;
use App\Services\User;
use App\Models\Admin;
use App\Exceptions\AuthException;

/**
 * Admin account provider
 * 
 * Provides authentication for admin model
 */
class AdminProvider implements AuthProviderInterface
{
    public function getRole()
    {
        return 'admin';
    }

    public function getRedirectRoute()
    {
        return 'dashboard.index';
    }

    public function getAllowedControllers()
    {
        return array(
            'App\Controllers\Dashboard\AdminsController',
            'App\Controllers\Dashboard\DepartmentsController',
            'App\Controllers\Dashboard\FacultyController',
            'App\Controllers\Dashboard\FacultyImportController',
            'App\Controllers\Dashboard\GradesController',
            'App\Controllers\Dashboard\GradesImportController',
            'App\Controllers\Dashboard\GuidanceController',
            'App\Controllers\Dashboard\HeadsController',
            'App\Controllers\Dashboard\MainController',
            'App\Controllers\Dashboard\MemosController',
            'App\Controllers\Dashboard\SectionsController',
            'App\Controllers\Dashboard\SettingsController',
            'App\Controllers\Dashboard\StudentsController',
            'App\Controllers\Dashboard\StudentsImportController',
        );
    }

    public function attempt($username, $password)
    {
        if (!$user = Admin::where('username', $username)->first()) {
            throw new AuthException('Invalid student number and/or password', AuthException::USER_NOT_FOUND);
        }

        if (!Hash::check($password, $user->password)) {
            throw new AuthException('Invalid student number and/or password', AuthException::INVALID_PASSWORD);
        }

        $user->last_login_at = date('Y-m-d H:i:s');

        if (Hash::needsRehash($user->password)) {
            $user->password = Hash::make($password);
        }

        $user->save();

        return new User($this, $user);
    }
}
