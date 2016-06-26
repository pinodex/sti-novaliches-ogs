<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions\User\Providers;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Exceptions\AuthException;
use App\Models\Student;

class MultiRoleUserProvider implements UserProvider
{
    /**
     * @var array User roles
     */
    private $roles = [];

    public function __construct(array $roles)
    {
        $this->roles = $roles;
    }

    public function retrieveById($identifier)
    {
        $role = explode(':', $identifier)[0];
        $id = explode(':', $identifier)[1];

        if (array_key_exists($role, $this->roles)) {
            $roleClass = $this->roles[$role];

            return (new $roleClass)->retrieveById($id);
        }

        return null;
    }

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        return null;
    }

    public function retrieveByCredentials(array $credentials)
    {
        $lastException = null;

        foreach ($this->roles as $role => $roleClass) {
            try {
                return (new $roleClass)->retrieveByCredentials($credentials);
            } catch (AuthException $exception) {
                if ($exception->getCode() != AuthException::USER_NOT_FOUND) {
                    throw $exception;
                }

                $lastException = $exception;
            }
        }

        if ($lastException) {
            throw $lastException;
        }

        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $lastException = null;

        foreach ($this->roles as $role => $roleClass) {
            try {
                return (new $roleClass)->validateCredentials($user, $credentials);
            } catch (AuthException $exception) {
                if ($exception->getCode() == AuthException::ACCOUNT_LOCKED) {
                    throw $exception;
                }

                $lastException = $exception;
            }
        }

        if ($lastException) {
            throw $lastException;
        }
    }
}
