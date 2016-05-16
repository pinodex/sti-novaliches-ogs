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
use App\Services\Hash;

/**
 * Memo model
 * 
 * Model class for memos table
 */
class Memo extends Model
{
    protected $fillable = array(
        'admin_id',
        'faculty_id',
        'subject',
        'content'
    );

    /**
     * Get admin associated with the memo
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function admin()
    {
        return $this->belongsTo('App\Models\Admin');
    }

    /**
     * Get faculty associated with the memo
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function faculty()
    {
        return $this->belongsTo('App\Models\Faculty');
    }
}
