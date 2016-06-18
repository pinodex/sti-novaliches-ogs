<?php

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
