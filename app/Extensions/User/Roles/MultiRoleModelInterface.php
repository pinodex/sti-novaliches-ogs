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

interface MultiRoleModelInterface
{
    /**
     * Get redirect route for specific role
     * 
     * @return string
     */
    public function getRedirectRoute();

    /**
     * Get user role
     * 
     * @return string
     */
    public function getRole();
}
