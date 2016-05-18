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
use Illuminate\Database\Capsule\Manager as DB;
use App\Traits\RelationSearchableTrait;
use App\Traits\ChoosableTrait;

/**
 * Department model
 * 
 * Model class for departments table
 */
class Department extends Model
{
    use RelationSearchableTrait, ChoosableTrait;

    public $timestamps = false;

    protected $fillable = array(
        'name',
        'head'
    );

    /**
     * Get department head
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function head()
    {
        return $this->hasOne('App\Models\Head');
    }

    /**
     * Get department faculty
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function faculty()
    {
        return $this->hasMany('App\Models\Faculty');
    }
}
