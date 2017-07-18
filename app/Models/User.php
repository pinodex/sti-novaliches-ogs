<?php

/*
 * This file is part of the SIS for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Models;

use Illuminate\Http\Request as HttpRequest;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use App\Components\Auth\Providers\UserProvider;
use App\Components\Auth\Authenticatable;
use App\Traits\PasswordHashable;
use App\Traits\SearchableName;
use App\Traits\WithPicture;
use App\Components\Acl;

class User extends Authenticatable
{
    use SoftDeletes,
        PasswordHashable,
        SearchableName,
        WithPicture,
        Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'department_id',
        'group_id',
        'username',
        'password',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'require_password_change'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'last_login_at',
        'deleted_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    protected $appends = [
        'name'
    ];

    /**
     * Get user group
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the associated department model
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the employee headed department model
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function headedDepartment()
    {
        return $this->hasOne(Department::class, 'head_id');
    }

    public function getProviderClass()
    {
        return UserProvider::class;
    }

    public function getProviderIdentifier()
    {
        return 'user';
    }

    public function getRedirectRoute()
    {
        return 'dashboard.index';
    }

    public function getRequirePasswordChangeAttribute($value)
    {
        return $value == 1;
    }
    
    public function setAttribute($key, $value)
    {
        $isRememberTokenAttribute = $key == $this->getRememberTokenName();

        if (!$isRememberTokenAttribute) {
            parent::setAttribute($key, $value);
        }
    }

    public function setGroupIdAttribute($value)
    {
        if ($value == 0) {
            $value = null;
        }

        $this->attributes['group_id'] = $value;
    }

    public function setDepartmentIdAttribute($value)
    {
        if ($value == 0) {
            $value = null;
        }

        $this->attributes['department_id'] = $value;
    }
}
