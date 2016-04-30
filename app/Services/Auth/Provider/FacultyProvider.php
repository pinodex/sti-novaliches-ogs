<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Services\Auth\Provider;

use App\Services\Hash;
use App\Services\Auth\User;
use App\Models\Faculty;

/**
 * Faculty provider
 * 
 * Provides authentication for faculty model
 */
class FacultyProvider implements AuthProviderInterface
{
    public function getRole()
    {
        return 'faculty';
    }

    public function getRedirectRoute()
    {
        return 'dashboard.index';
    }

    public function getAllowedControllers()
    {
        return array(
            'App\Controllers\MainController',
            'App\Controllers\Faculty\MainController',
            'App\Controllers\Faculty\GradesController',
            'App\Controllers\Faculty\StudentController'
        );
    }
    
    public function attempt($username, $password)
    {
        if ($user = Faculty::where('username', $username)->first()) {
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
