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
 * Provides mutation of date attributes
 */
trait HumanReadableDateTrait
{
    /**
     * Format created_at attribute
     * 
     * @param string $value Input date
     * 
     * @return string
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->format($value);
    }

    /**
     * Format updated_at attribute
     * 
     * @param string $value Input date
     * 
     * @return string
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->format($value);
    }

    /**
     * Format last_login_at attribute
     * 
     * @param string $value Input date
     * 
     * @return string
     */
    public function getLastLoginAtAttribute($value)
    {
        return $this->format($value);
    }

    /**
     * Format ISO timestamp to human-readable format
     * 
     * @param string $value Timestamp
     * 
     * @return string
     */
    private function format($timestamp)
    {
        if (!$timestamp) {
            return 'N/A';
        }

        return date('M d, Y h:i a', strtotime($timestamp));
    }
}
