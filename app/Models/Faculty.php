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
 * Model class for faculties table
 */
class Faculty extends Model
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
    public function head()
    {
        return $this->belongsTo('App\Models\Head', 'department_id', 'department_id');
    }

    /**
     * Get associated sections
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function sections()
    {
        return $this->belongsToMany('App\Models\Section');
    }

    protected $appends = array(
        'name', 'is_grade_submission_deadline_due'
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

    public function getIsGradeSubmissionDeadlineDueAttribute()
    {
        $deadline = $this->department->getOriginal('grade_submission_deadline');
        $submissionDate = $this->getOriginal('last_grade_submission_at');

        if (!$deadline || !$submissionDate) {
            return false;
        }

        return strtotime($deadline) < strtotime($submissionDate);
    }

    /**
     * Format last_grade_submission_at attribute
     * 
     * @param string $value Input date
     * 
     * @return string
     */
    public function getLastGradeSubmissionAtAttribute($value)
    {
        if (!$value) {
            return 'N/A';
        }

        return date('M d, Y h:i a', strtotime($value));
    }
}
