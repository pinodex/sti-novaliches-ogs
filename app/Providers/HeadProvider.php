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

/**
 * Head provider
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
            
            'App\Controllers\Dashboard\FacultiesController' => array(
                'view'
            ),
            
            'App\Controllers\Dashboard\StudentsController' => array(
                'index',
                'view'
            ),

            'App\Controllers\Dashboard\SectionsController' => array(
                'index'
            ),

            'App\Controllers\Dashboard\GradesController' => array(
                'index',
            )
        );
    }
    
    public function attempt($username, $password)
    {
        if ($user = Head::where('username', $username)->first()) {
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
