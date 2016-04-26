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
use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;

/**
 * Admin provider
 * 
 * Provides user model and authentication for admin users
 */
class AdminProvider implements AuthProviderInterface
{
    public function getRole()
    {
        return 'admin';
    }

    public function getRedirectRoute()
    {
        return 'admin.index';
    }

    public function getAllowedControllers()
    {
        return array(
            'App\Controllers\MainController',
            'App\Controllers\Admin\MainController',
            'App\Controllers\Admin\ManageAdminController',
            'App\Controllers\Admin\ManageFacultyController',
            'App\Controllers\Admin\ManageStudentController',
            'App\Controllers\Faculty\MainController',
            'App\Controllers\Faculty\GradesController',
            'App\Controllers\Faculty\StudentController'
        );
    }

    public function getName(User $user)
    {
        $model = $user->getModel();

        return sprintf('%s, %s %s',
            $model->last_name, $model->first_name, $model->middle_name
        );
    }

    public function attempt($username, $password)
    {
        if ($user = Admin::where('username', $username)->first()) {
            if (!Hash::check($password, $user->password)) {
                return false;
            }

            if (Hash::needsRehash($user->password)) {
                $user->password = Hash::make($password);
                $user->save();
            }

            return new User($this, $user);
        }

        return false;
    }
}
