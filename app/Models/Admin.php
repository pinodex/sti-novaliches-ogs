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
 * Admin model
 * 
 * Model class for admins table
 */
class Admin extends Model implements Authenticatable, MultiRoleModelInterface
{
    use HumanReadableDateTrait,
        HashablePasswordTrait,
        ConcatenateNameTrait,
        SearchableTrait,
        ChoosableTrait;
    
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

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return 'admin:' . $this->attributes['id'];
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
        return 'admin';
    }

    /**
     * Get associated memos
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function memos()
    {
        return $this->hasMany('App\Models\Memo');
    }
}
