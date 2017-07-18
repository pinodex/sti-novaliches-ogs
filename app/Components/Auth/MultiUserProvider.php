<?php

/*
 * This file is part of the SIS for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\Auth;

use Hash;
use App\Models\User;
use Illuminate\Contracts\Auth\UserProvider as BaseUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Components\Auth\Providers\UserProvider;
use App\Components\Auth\Providers\StudentProvider;

class MultiUserProvider implements BaseUserProvider
{
    public function retrieveById($identifier)
    {
        $provider = explode(':', $identifier)[0];
        $id = explode(':', $identifier)[1];

        switch ($provider) {
            case 'user':
                return (new UserProvider)->retrieveById($id);
                break;

            case 'student':
                return (new StudentProvider)->retrieveById($id);
                break;
        }

        return null;
    }

    public function retrieveByToken($identifier, $token)
    {

    }

    public function updateRememberToken(Authenticatable $user, $token)
    {

    }

    public function retrieveByCredentials(array $credentials)
    {
        if (is_numeric($credentials['id'])) {
            return (new StudentProvider)->retrieveByCredentials($credentials);
        }

        return (new UserProvider)->retrieveByCredentials($credentials);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $provider = $user->getProviderClass();

        return (new $provider)->validateCredentials($user, $credentials);
    }
}
