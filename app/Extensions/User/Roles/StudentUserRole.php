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
use App\Models\Student;
use App\Exceptions\AuthException;
use Illuminate\Contracts\Auth\Authenticatable;

class StudentUserRole implements UserRoleInterface
{
    public function getModelClass()
    {
        return Student::class;
    }

    public function retrieveById($identifier)
    {
        return Student::find(parseStudentId($identifier));
    }

    public function retrieveByCredentials(array $credentials)
    {
        $user = Student::find(parseStudentId($credentials['id']));

        if ($user) {
            return $user;
        }

        throw new AuthException('Invalid ID and/or password', AuthException::USER_NOT_FOUND);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // Use password if student has password, else use their middle name.
        if ($user->password !== null) {
            if (Hash::check($credentials['password'], $user->password)) {
                if (Hash::needsRehash($user->password)) {
                    $user->password = Hash::make($credentials['password']);
                    $user->save();
                }

                return true;
            }

            throw new AuthException('Invalid ID and/or password', AuthException::INVALID_PASSWORD);
        }

        $userPassword = $user->middle_name;

        if (empty($userPassword) || $userPassword == '.') {
            $userPassword = $user->last_name;
        }

        if ($this->checkPassword($credentials['password'], $userPassword)) {
            $user->last_login_at = date('Y-m-d H:i:s');
            $user->save();

            return true;
        }

        throw new AuthException('Invalid ID and/or password', AuthException::INVALID_PASSWORD);
    }

    private function checkPassword($enteredPassword, $password) {
        return strtoupper($enteredPassword) == strtoupper($password)  ||
            strtoupper($enteredPassword) == strtoupper(normalizeAccents($password));
    }
}
