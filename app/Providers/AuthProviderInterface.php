<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Providers;

/**
 * Interface for auth providers
 */
interface AuthProviderInterface
{
    /**
     * Get provider account role
     * 
     * @return string
     */
    public function getRole();

    /**
     * Get route to redirect after login
     * 
     * @return string
     */
    public function getRedirectRoute();

    /**
     * Get protected route group the user has access to
     * 
     * @return object[]
     */
    public function getAllowedControllers();

    /**
     * Attempt to login
     * 
     * @return boolean|\App\Services\User
     */
    public function attempt($username, $password);
}
