<?php

/*
 * This file is part of the SIS for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\Auth;

use Illuminate\Foundation\Auth\User;
use App\Components\Acl;

abstract class Authenticatable extends User
{
    /**
     * Get user provider class for this model
     * 
     * @return string
     */
    abstract public function getProviderClass();

    /**
     * Get user provider id for this model
     * 
     * @return string
     */
    abstract public function getProviderIdentifier();

    /**
     * Get redirect route
     * 
     * @return string
     */
    abstract public function getRedirectRoute();

    public function getAuthIdentifier()
    {
        return $this->getProviderIdentifier() . ':' . $this->attributes['id'];
    }

    /**
     * Check if user has granted permissions
     * 
     * @param string $permissions,... Permission name
     * 
     * @return boolean
     */
    public function canDo($permissions)
    {
        return Acl::for($this)->can($permissions);
    }

    /**
     * Get full name from name components
     * 
     * @return string
     */
    public function getNameAttribute()
    {
        return sprintf('%s %s',
            $this->first_name,
            $this->last_name
        );
    }
}
