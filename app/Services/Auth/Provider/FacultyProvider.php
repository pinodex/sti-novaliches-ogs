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
use App\Routes\FacultyRoute;
use Illuminate\Database\Eloquent\Model;

/**
 * Faculty provider
 * 
 * Provides user model and authentication for faculty users
 */
class FacultyProvider implements AuthProviderInterface
{
    public function getRole()
    {
        return 'faculty';
    }

    public function getRedirectRoute()
    {
        return 'faculty.index';
    }

    public function getAllowedRouteGroup()
    {
        return array(
            FacultyRoute::class
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
        if ($user = Faculty::where('username', $username)->first()) {
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
