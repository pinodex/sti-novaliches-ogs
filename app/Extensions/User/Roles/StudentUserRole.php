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

use App\Models\Student;
use App\Exceptions\AuthException;
use Illuminate\Contracts\Auth\Authenticatable;

class StudentUserRole implements UserRoleInterface
{
    public function retrieveById($identifier)
    {
        return Student::find(parseStudentId($identifier));
    }

    public function retrieveByCredentials(array $credentials)
    {
        $id = null;

        if (isset($credentials['id'])) {
            $id = $credentials['id'];
        } else if (isset($credentials['email'])) {
            $id = $credentials['email'];
        }

        $user = Student::find(parseStudentId($id));

        if ($user) {
            return $user;
        }

        throw new AuthException('Invalid ID and/or password', AuthException::USER_NOT_FOUND);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // As a security measure, don't allow access to students with no middle name
        if (empty($user->middle_name) || $user->middle_name == '.') {
            throw new AuthException('Your account is temporarily locked.', AuthException::ACCOUNT_LOCKED);
        }

        if (strtoupper($credentials['password']) == $user->middle_name  ||
            strtoupper($credentials['password']) == normalizeAccents($user->middle_name)) {

            return true;
        }

        throw new AuthException('Invalid ID and/or password', AuthException::INVALID_PASSWORD);
    }
}
