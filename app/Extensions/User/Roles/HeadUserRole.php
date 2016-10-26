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
use App\Models\Head;
use App\Exceptions\AuthException;
use Illuminate\Contracts\Auth\Authenticatable;

class HeadUserRole implements UserRoleInterface
{
    public function getModelClass()
    {
        return Head::class;
    }

    public function retrieveById($identifier)
    {
        return Head::find($identifier);
    }

    public function retrieveByCredentials(array $credentials)
    {
        $id = null;

        if (isset($credentials['id'])) {
            $id = $credentials['id'];
        } else if (isset($credentials['email'])) {
            $id = $credentials['email'];
        }

        $user = Head::where('username', $id)->first();

        if ($user) {
            return $user;
        }

        throw new AuthException('Invalid ID and/or password', AuthException::USER_NOT_FOUND);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        if (Hash::check($credentials['password'], $user->password)) {
            if (Hash::needsRehash($user->password)) {
                $user->password = Hash::make($credentials['password']);
            }
            
            $user->last_login_at = date('Y-m-d H:i:s');
            $user->save();
            
            return true;
        }

        throw new AuthException('Invalid ID and/or password', AuthException::INVALID_PASSWORD);
    }
}
