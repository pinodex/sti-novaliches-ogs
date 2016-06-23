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

use Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Extensions\User\Roles\MultiRoleModelInterface;
use App\Traits\HumanReadableDateTrait;
use App\Traits\HashablePasswordTrait;
use App\Traits\ConcatenateNameTrait;
use App\Traits\SearchableTrait;
use App\Extensions\Settings;

/**
 * Head model
 * 
 * Model class for guidances table
 */
class Guidance extends Model implements Authenticatable, MultiRoleModelInterface
{
    use HumanReadableDateTrait,
        HashablePasswordTrait,
        ConcatenateNameTrait,
        SearchableTrait;

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
        return 'guidance:' . $this->attributes['id'];
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
        return 'guidance';
    }
}
