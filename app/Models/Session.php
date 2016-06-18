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

/**
 * Session model
 * 
 * Model class for sessions table
 */
class Session extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = array(
        'id',
        'data',
        'expiry'
    );
}
