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
use App\Models\Faculty;
use App\Exceptions\AuthException;

/**
 * Faculty account provider
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
            'App\Controllers\Dashboard\MainController',
            
            'App\Controllers\Dashboard\StudentsController' => array(
                'index',
                'view'
            ),
            
            'App\Controllers\Dashboard\GradesImportController' => array(
                'index',
                'stepOne',
                'stepTwo',
                'stepThree',
                'stepFour',
            ),

            'App\Controllers\Dashboard\MemosController' => array(
                'index',
                'view'
            )
        );
    }
    
    public function attempt($username, $password)
    {
        if (!$user = Faculty::where('username', $username)->first()) {
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
