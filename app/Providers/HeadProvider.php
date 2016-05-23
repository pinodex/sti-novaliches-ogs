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
use App\Models\Head;
use App\Exceptions\AuthException;

/**
 * Head account provider
 * 
 * Provides authentication for head model
 */
class HeadProvider implements AuthProviderInterface
{
    public function getRole()
    {
        return 'head';
    }

    public function getRedirectRoute()
    {
        return 'dashboard.index';
    }

    public function getAllowedControllers()
    {
        return array(
            'App\Controllers\Dashboard\MainController',

            'App\Controllers\Dashboard\DepartmentsController' => array(
                'self',
                'view',
                'settings'
            ),
            
            'App\Controllers\Dashboard\FacultyController' => array(
                'view'
            ),
            
            'App\Controllers\Dashboard\StudentsController' => array(
                'index',
                'view'
            ),

            'App\Controllers\Dashboard\SectionsController',
        );
    }
    
    public function attempt($username, $password)
    {
        if (!$user = Head::where('username', $username)->first()) {
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
