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
 * Setting model
 * 
 * Model class for settings table
 */
class Setting extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = array(
        'id',
        'value'
    );
}
