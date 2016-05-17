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

    protected $appends = array(
        'is_unread'
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

    /**
     * Getter for is_unread
     * 
     * @return boolean
     */
    public function getIsUnreadAttribute()
    {
        return $this->opened_at === null;
    }

    /**
     * Search memo
     * 
     * @param string $subject Memo subject
     * @param int $adminId Admin associated with memo
     * @param int $facultyId Faculty associated with memo
     * 
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function search($subject = null, $adminId = null, $facultyId = null)
    {
        $memos = static::with('faculty', 'admin');

        if ($subject) {
            $memos->where('subject', 'LIKE', '%' . $subject . '%');
        }

        if ($adminId) {
            $memos->where('admin_id', $adminId);
        }

        if ($facultyId) {
            $memos->where('faculty_id', $facultyId);
        }

        return $memos->orderBy('id', 'DESC')->paginate();
    }
}
