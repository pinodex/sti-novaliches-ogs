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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Capsule\Manager as DB;
use App\Traits\ChoosableTrait;

/**
 * Department model
 * 
 * Model class for departments table
 */
class Department extends Model
{
    use SoftDeletes, ChoosableTrait;

    public $timestamps = false;

    protected $fillable = ['name', 'head'];

    /**
     * Get department head
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function head()
    {
        return $this->hasOne(Head::class);
    }

    /**
     * Get department faculty
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function faculty()
    {
        return $this->hasMany(Faculty::class);
    }
}
