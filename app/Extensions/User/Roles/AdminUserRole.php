<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions\User\Roles;

use Hash;
use App\Models\Admin;
use App\Exceptions\AuthException;
use Illuminate\Contracts\Auth\Authenticatable;

class AdminUserRole implements UserRoleInterface
{
    public function retrieveById($identifier)
    {
        return Admin::find($identifier);
    }

    public function retrieveByCredentials(array $credentials)
    {
        $user = Admin::where('username', $credentials['id'])->first();

        if ($user) {
            return $user;
        }

        throw new AuthException('Invalid ID and/or password', AuthException::USER_NOT_FOUND);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        if (Hash::check($credentials['password'], $user->password)) {
            return true;
        }

        throw new AuthException('Invalid ID and/or password', AuthException::INVALID_PASSWORD);
    }
}
