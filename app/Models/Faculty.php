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
use App\Traits\ChoosableTrait;
use App\Services\Settings;

/**
 * Head model
 * 
 * Model class for faculty table
 */
class Faculty extends Model
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
        'department_id'
    );

    protected $hidden = array(
        'password'
    );

    protected $appends = array(
        'name',
        'status',
        'is_valid'
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
    public function getStatusAttribute($period = null)
    {
        if ($this->getIsNeverSubmittedAttribute($period)) {
            return 'Never submitted';
        }

        if ($this->getIsIncompleteAttribute($period)) {
            return 'Incomplete';
        }

        if ($this->getIsSubmittedLateAttribute($period)) {
            return 'Submitted late';
        }

        return 'Submitted';
    }

    /**
     * Get date of first grade import
     * 
     * @return string
     */
    public function getFirstGradeImportAtAttribute($period = null)
    {
        if ($first = $this->submissionLogs()->getQuery()
            ->where('period', $period ?: Settings::get('period'))->first()) {
            
            return $first->date;
        }

        return 'N/A';
    }

    /**
     * Get is_never_submitted attribute
     * 
     * @return boolean
     */
    public function getIsNeverSubmittedAttribute($period = null)
    {
        return $this->submissionLogs()->getQuery()
            ->where('period', $period ?: Settings::get('period'))->count() == 0;
    }

    /**
     * Get is_incomplete attribute
     * 
     * @return boolean
     */
    public function getIsIncompleteAttribute($period = null)
    {
        $isIncomplete = false;
        $period = strtolower($period ?: Settings::get('period', 'prelim')) . '_grade';

        $sheets = $this->submittedGrades->groupBy(function (Grade $grade) {
            return $grade->subject . ' ' . $grade->section;
        });

        foreach ($sheets as $grades) {
            $incompletes = 0;

            foreach ($grades as $grade) {
                if ($grade->getOriginal($period) === null) {
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
     * Get is_invalid attribute
     * 
     * @return boolean
     */
    public function getIsValidAttribute()
    {
        $isValid = true;

        $gradeGroups = $this->submittedGrades->groupBy(function (Grade $grade) {
            return $grade->subject . ' ' . $grade->section;
        });

        foreach ($gradeGroups as $id => $grades) {
            $totalCount = count($grades);
            $withoutGradesCount = array(
                'prelim'    => 0,
                'midterm'   => 0,
                'prefinal'  => 0,
                'final'     => 0
            );

            foreach ($grades as $grade) {
                if ($grade->getOriginal('prelim_grade') === null) {
                    $withoutGradesCount['prelim']++;
                }

                if ($grade->getOriginal('midterm_grade') === null) {
                    $withoutGradesCount['midterm']++;
                }

                if ($grade->getOriginal('prefinal_grade') === null) {
                    $withoutGradesCount['prefinal']++;
                }

                if ($grade->getOriginal('final_grade') === null) {
                    $withoutGradesCount['final']++;
                }
            }

            if (($withoutGradesCount['prelim'] != $totalCount && round($withoutGradesCount['prelim'] / $totalCount) >= 0.5) ||
                ($withoutGradesCount['midterm'] != $totalCount && round($withoutGradesCount['midterm'] / $totalCount) >= 0.5) ||
                ($withoutGradesCount['prefinal'] != $totalCount && round($withoutGradesCount['prefinal'] / $totalCount) >= 0.5) ||
                ($withoutGradesCount['final'] != $totalCount && round($withoutGradesCount['final'] / $totalCount) >= 0.5)) {

                $isValid = false;
            }
        }

        return $isValid;
    }

    /**
     * Get is_submitted_late attribute
     * 
     * @return boolean
     */
    public function getIsSubmittedLateAttribute($period = null)
    {
        $period = $period ?: Settings::get('period');
        $firstLog = $this->submissionLogs()->getQuery()->where('period', $period)->take(1)->first();

        if ($firstLog) {
            $deadline = Settings::getCurrentDeadline($period);

            if (!$deadline || $deadline == 'N/A') {
                return false;
            }

            return strtotime($deadline) < strtotime($firstLog->getOriginal('date'));
        }

        return false;
    }

    /**
     * Get number_of_fails attribute
     * 
     * @return integer
     */
    public function getNumberOfFailsAttribute($period = null)
    {
        $count = 0;
        $period = strtolower($period ?: Settings::get('period', 'prelim')) . '_grade';

        foreach ($this->submittedGrades as $grade) {
            if ($grade->getOriginal($period) !== null && $grade->getOriginal($period) < 75) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get number_of_drops attribute
     * 
     * @param string $period Period
     * 
     * @return integer
     */
    public function getNumberOfDropsAttribute($period = null)
    {
        $count = 0;
        $period = strtolower($period ?: Settings::get('period', 'prelim')) . '_grade';

        foreach ($this->submittedGrades as $grade) {
            if ($grade->getOriginal($period) == -1) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get name for choice display. Used by FormModelChoicesTrait
     * 
     * return string
     */
    public function getChoiceName()
    {
        return $this->name;
    }

    /**
     * Add submission log entry
     */
    public function addSubmissionLogEntry()
    {
        FacultyGradeImportLog::create(array(
            'faculty_id'    => $this->id,
            'period'        => Settings::get('period', 'PRELIM'),
            'date'          => date('Y-m-d H:i:s')
        ));
    }
}
