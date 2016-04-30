<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HumanReadableDateTrait;
use App\Traits\HashablePasswordTrait;
use App\Traits\SearchableTrait;

/**
 * Admin model
 * 
 * Model class for admins table
 */
class Admin extends Model
{
    use HumanReadableDateTrait, HashablePasswordTrait, SearchableTrait;
    
    protected $fillable = array(
        'username',
        'password',
        'last_name',
        'first_name',
        'middle_name',
    );

    protected $hidden = array(
        'password'
    );

    protected $appends = array(
        'name'
    );

    /**
     * Get full name
     * 
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->last_name . ', ' . $this->first_name;
    }
}
