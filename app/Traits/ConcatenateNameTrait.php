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

/**
 * Provides automatic name concatenation
 */
trait ConcatenateNameTrait
{
    /**
     * Get full name
     * 
     * @return string
     */
    public function getNameAttribute()
    {
        return ucwords($this->last_name . ', ' . $this->first_name);
    }
}
