<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Traits;

use Hash;

/**
 * Provides password attribute mutation for hashing
 */
trait HashablePasswordTrait
{
    /**
     * Auto-hash incoming password
     * 
     * @param string $password Password
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }
}
