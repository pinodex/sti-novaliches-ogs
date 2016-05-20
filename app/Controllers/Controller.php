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
use App\Services\Csrf;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * Is user role equals to $role parameter given
     * 
     * @param string $role User role
     * 
     * @return boolean
     */
    protected function isRole($role)
    {
        if ($this->isNotLoggedIn()) {
            return false;
        }

        return $this->user->getRole() == $role;
    }

    /**
     * Check if user is logged in
     * 
     * @return boolean
     */
    protected function isLoggedIn()
    {
        return $this->user !== null;
    }

    /**
     * Check if user is not logged in
     * 
     * @return boolean
     */
    protected function isNotLoggedIn()
    {
        return $this->user === null;
    }

    protected function isTokenValid($identifier, Request $request)
    {
        return Csrf::isValid($identifier, $request->request->get('_token'));
    }
}
