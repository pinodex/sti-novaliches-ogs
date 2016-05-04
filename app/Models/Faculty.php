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
use App\Traits\ConcatenateNameTrait;
use App\Traits\SearchableTrait;
use App\Services\Settings;

/**
 * Head model
 * 
 * Model class for faculty table
 */
class Faculty extends Model
{
    use HumanReadableDateTrait, HashablePasswordTrait, ConcatenateNameTrait, SearchableTrait;

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

    protected $appends = array(
        'name',
        'status',
        'first_grade_import_at',
        'is_never_submitted',
        'is_submitted_late',
        'is_incomplete',
        'number_of_fails',
        'number_of_drops'
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
     * Get associated faculty
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function head()
    {
        return $this->belongsTo('App\Models\Head', 'department_id', 'department_id');
    }

    /**
     * Get associated submission log
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function submissionLogs()
    {
        return $this->hasMany('App\Models\FacultyGradeImportLog');
    }

    /**
     * Get submitted grades
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function submittedGrades()
    {
        return $this->hasMany('App\Models\Grade', 'importer_id');
    }

    /**
     * Get status attribute
     * 
     * @return string
     */
    public function getStatusAttribute()
    {
        if ($this->is_incomplete) {
            return 'Incomplete';
        }

        if ($this->is_never_submitted) {
            return 'Never submitted';
        }

        if ($this->is_submitted_late) {
            return 'Submitted late';
        }

        return 'Submitted';
    }

    /**
     * Get date of first grade import
     * 
     * @return string
     */
    public function getFirstGradeImportAtAttribute()
    {
        if ($first = $this->submissionLogs->first()) {
            return $first->date;
        }

        return 'N/A';
    }

    /**
     * Get is_never_submitted attribute
     * 
     * @return string
     */
    public function getIsNeverSubmittedAttribute()
    {
        return $this->submissionLogs->count() == 0;
    }

    /**
     * Get is_incomplete attribute
     * 
     * @return string
     */
    public function getIsIncompleteAttribute()
    {
        $isIncomplete = false;
        $period = strtolower(Settings::get('period', 'prelim')) . '_grade';

        $sheets = $this->submittedGrades->groupBy(function (Grade $grade) {
            return $grade->subject . ' ' . $grade->section;
        });

        foreach ($sheets as $grades) {
            $incompletes = 0;

            foreach ($grades as $grade) {
                if ($grade->getAttribute($period) === null) {
                    $incompletes++;
                }
            }

            if ($incompletes == count($grades)) {
                $isIncomplete = true;
                
                break;
            }
        }

        return $isIncomplete;
    }

    /**
     * Get is_submitted_late attribute
     * 
     * @return string
     */
    public function getIsSubmittedLateAttribute()
    {
        $logs = $this->submissionLogs;

        if ($logs->count()) {
            $deadline = $this->department->getOriginal('grade_submission_deadline');
            $submissionDate = $logs->first()->date;

            if (!$deadline || !$submissionDate) {
                return false;
            }

            return strtotime($deadline) < strtotime($submissionDate);
        }

        return false;
    }

    /**
     * Get number_of_fails attribute
     * 
     * @return string
     */
    public function getNumberOfFailsAttribute()
    {
        $count = 0;
        $period = strtolower(Settings::get('period', 'prelim')) . '_grade';

        foreach ($this->submittedGrades as $grade) {
            if ($grade->getAttribute($period) !== null && $grade->getAttribute($period) < 75) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get number_of_drops attribute
     * 
     * @return string
     */
    public function getNumberOfDropsAttribute()
    {
        $count = 0;
        $period = strtolower(Settings::get('period', 'prelim')) . '_grade';

        foreach ($this->submittedGrades as $grade) {
            if ($grade->getAttribute($period) == -1) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Add submission log entry
     */
    public function addSubmissionLogEntry()
    {
        FacultyGradeImportLog::create(array(
            'faculty_id'    => $this->id,
            'date'          => date('Y-m-d H:i:s')
        ));
    }
}
