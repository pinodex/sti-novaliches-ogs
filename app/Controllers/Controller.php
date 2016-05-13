<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers;

use App\Services\Auth;

/**
 * Base controller
 */
class Controller
{
    /**
     * @var \App\Services\User User instance
     */
    protected $user;

    /**
     * @var \Illuminate\Database\Eloquent\Model User model instance
     */
    protected $userModel;

    /**
     * Constructs base controller class
     */
    public function __construct()
    {
        $this->user = Auth::user();
    }

    protected function isRole($role)
    {
        if ($this->isNotLoggedIn()) {
            return false;
        }

        return $this->user->getRole() == $role;
    }

    protected function isLoggedIn()
    {
        return $this->user !== null;
    }

    protected function isNotLoggedIn()
    {
        return $this->user === null;
    }
}
