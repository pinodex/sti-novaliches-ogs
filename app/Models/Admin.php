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
use App\Services\Hash;

/**
 * Admin model
 * 
 * Admin model for admins table
 */
class Admin extends Model
{
    public $timestamps = false;

    protected $fillable = ['username', 'password', 'first_name', 'middle_name', 'last_name', 'type'];

    protected $hidden = ['password'];

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
