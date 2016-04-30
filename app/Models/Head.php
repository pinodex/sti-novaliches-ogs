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
use App\Services\Hash;

/**
 * Head model
 * 
 * Model class for heads table
 */
class Head extends Model
{
    use HumanReadableDateTrait, HashablePasswordTrait, SearchableTrait;

    protected $fillable = array(
        'username',
        'password',
        'last_name',
        'first_name',
        'middle_name',
        'department_id'
    );

    protected $hidden = array(
        'password'
    );

    private static $searchWithRelations = array(
        'department'
    );

    /**
     * Get department
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function department()
    {
        return $this->belongsTo('App\Models\Department');
    }

    /**
     * Get associated faculties
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function faculties()
    {
        return $this->hasMany('App\Models\Faculty', 'department_id', 'department_id');
    }

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
