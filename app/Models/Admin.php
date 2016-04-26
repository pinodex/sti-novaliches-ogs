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
use App\Services\Hash;

/**
 * Admin model
 * 
 * Admin model for admins table
 */
class Admin extends Model
{
    public $timestamps = false;

    protected $fillable = array(
        'username',
        'password',
        'first_name',
        'middle_name',
        'last_name',
        'department'
    );

    protected $hidden = array('password');

    /**
     * Auto-hash incoming password
     * 
     * @param string $password Password
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    /**
     * Search admin
     * 
     * @param string $id    Student ID
     * @param string $name  Student name
     * 
     * @param array
     */
    public static function search($name = null)
    {
        $nameFormats = array(
            DB::raw("CONCAT(last_name, ' ', first_name, ' ', middle_name)"),
            DB::raw("CONCAT(last_name, ', ', first_name, ' ', middle_name)"),
            DB::raw("CONCAT(first_name, ' ', middle_name, ' ', last_name)"),
            DB::raw("CONCAT(first_name, ' ', last_name)")
        );

        $result = self::where($nameFormats[0], 'LIKE', '%' . $name . '%')
            ->orWhere($nameFormats[1], 'LIKE', '%' . $name . '%')
            ->orWhere($nameFormats[2], 'LIKE', '%' . $name . '%')
            ->orWhere($nameFormats[3], 'LIKE', '%' . $name . '%');

        return $result->paginate(50);
    }
}
