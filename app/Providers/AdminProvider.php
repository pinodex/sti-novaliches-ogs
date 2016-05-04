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
            'App\Controllers\Dashboard\MainController',
            'App\Controllers\Dashboard\AdminsController',
            'App\Controllers\Dashboard\HeadsController',
            'App\Controllers\Dashboard\FacultiesController',
            'App\Controllers\Dashboard\GuidanceController',
            'App\Controllers\Dashboard\DepartmentsController',
            'App\Controllers\Dashboard\StudentsController',
            'App\Controllers\Dashboard\GradesController',
            'App\Controllers\Dashboard\SettingsController'
        );
    }

    public function attempt($username, $password)
    {
        if ($user = Admin::where('username', $username)->first()) {
            if (!Hash::check($password, $user->password)) {
                return false;
            }

            $user->last_login_at = date('Y-m-d H:i:s');

            if (Hash::needsRehash($user->password)) {
                $user->password = Hash::make($password);
            }

            $user->save();

            return new User($this, $user);
        }

        return false;
    }
}
