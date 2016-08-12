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
use Illuminate\Contracts\Auth\Authenticatable;
use App\Extensions\User\Roles\MultiRoleModelInterface;
use App\Traits\HumanReadableDateTrait;
use App\Traits\HashablePasswordTrait;
use App\Traits\ConcatenateNameTrait;
use App\Traits\SearchableTrait;
use App\Traits\ChoosableTrait;

/**
 * Head model
 * 
 * Model class for heads table
 */
class Head extends Model implements Authenticatable, MultiRoleModelInterface
{
    use HumanReadableDateTrait,
        HashablePasswordTrait,
        ConcatenateNameTrait,
        SearchableTrait,
        ChoosableTrait;

    protected $fillable = [
        'username',
        'password',
        'last_name',
        'first_name',
        'middle_name',
        'department_id'
    ];

    protected $hidden = ['password'];

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return 'head:' . $this->attributes['id'];
    }

    public function getAuthPassword()
    {
        return $this->attributes['password'];
    }

    public function getRememberToken() {}

    public function setRememberToken($value) {}

    public function getRememberTokenName() {}

    public function getRedirectRoute()
    {
        return 'dashboard.index';
    }

    public function getRole()
    {
        return 'head';
    }

    /**
     * Get department
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get associated faculty
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function faculty()
    {
        return $this->hasMany(Faculty::class, 'department_id', 'department_id');
    }

    protected $appends = [
        'name'
    ];
}
