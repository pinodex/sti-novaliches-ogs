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

use App\Models\Student;
use App\Services\Auth\User;

/**
 * Student provider
 * 
 * Provides user model and authentication for student users
 */
class StudentProvider implements AuthProviderInterface
{
    public function getRole()
    {
        return 'student';
    }

    public function getRedirectRoute()
    {
        return 'student.index';
    }

    public function getAllowedControllers()
    {
        return array(
            'App\Controllers\MainController',
            'App\Controllers\Student\MainController'
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
        $user = Student::where(array(
            'id' => self::parseId($username),
            'middle_name' => strtoupper($password)
        ))->first();

        if ($user) {
            return new User($this, $user);
        }

        return false;
    }

    private static function parseId($id)
    {
        /**
         * Pattern definition:
         * 
         * XXX-XXXX-XXXX OR XXXXXXXXXXX
         * where X is a number from 0 to 9.
         * 
         * Example matches:
         *  - 021-2015-0330
         *  - 02120150330
         */

        if (preg_match('/([\d+]{3}-[\d+]{4}-[\d+]{4})|([\d+]{3})([\d+]{4})([\d+]{4})/', $id)) {
            return str_replace('-', '', $id);
        }

        return $id;
    }
}
