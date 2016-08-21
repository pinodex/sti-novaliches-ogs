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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Extensions\User\Roles\MultiRoleModelInterface;
use App\Traits\HumanReadableDateTrait;
use App\Traits\HashablePasswordTrait;
use App\Traits\ConcatenateNameTrait;
use App\Traits\SearchableTrait;
use App\Traits\ChoosableTrait;
use App\Extensions\Settings;
use App\Extensions\SgrReporter;

/**
 * Head model
 * 
 * Model class for faculty table
 */
class Faculty extends Model implements Authenticatable, MultiRoleModelInterface
{
    use SoftDeletes,
        HumanReadableDateTrait,
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

    protected $appends = ['name'];

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return 'faculty:' . $this->attributes['id'];
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
        return 'faculty';
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
    public function head()
    {
        return $this->belongsTo(Head::class, 'department_id', 'department_id');
    }

    /**
     * Get associated memos
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function memos()
    {
        return $this->hasMany(Memo::class);
    }

    /**
     * Get associated submission log
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function submissionLogs()
    {
        return $this->hasMany(GradeImportLog::class);
    }

    /**
     * Get submitted grades
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function submittedGrades()
    {
        return $this->hasMany(Grade::class, 'importer_id');
    }

    /**
     * Get unread memo count by faculty
     * 
     * @return int
     */
    public function getUnreadMemoCount()
    {
        return $this->memos()->getQuery()->whereNull('opened_at')->count();
    }

    /**
     * Get faculty status
     * 
     * @param string $period Grading period
     * 
     * @return string
     */
    public function getStatus($period = null)
    {
        if ($this->isNeverSubmitted($period)) {
            return 'Never submitted';
        }

        if ($this->isIncomplete($period)) {
            return 'Incomplete';
        }

        if ($this->isSubmittedLate($period)) {
            return 'Submitted late';
        }

        return 'Submitted';
    }

    /**
     * Check if faculty has never submitted a grade
     * 
     * @param string $period Grading period
     * 
     * @return boolean
     */
    public function isNeverSubmitted($period = null)
    {
        if ($period === null) {
            $period = Settings::get('period', 'prelim');
        }

        $status = true;

        $this->submissionLogs->each(function (GradeImportLog $log) use (&$status, $period) {
            if (strtolower($log->period) == strtolower($period)) {
                $status = false;

                return;
            }
        });

        return $status;
    }

    /**
     * Check if faculty is incomplete
     * 
     * @param string $period Grading period
     * 
     * @return boolean
     */
    public function isIncomplete($period = null)
    {
        $isIncomplete = false;

        return $isIncomplete;
    }

    /**
     * Check if faculty submission is valid
     * 
     * @param string $period Grading period
     * 
     * @return boolean
     */
    public function isValid($period = null)
    {
        if ($period === null) {
            $period = Settings::get('period', 'prelim');
        }

        $period = strtoupper($period);

        // Get latest submission from import logs
        $latestSubmission = $this->submissionLogs->where('period', $period)->sortByDesc('date');

        if ($entry = $latestSubmission->first()) {
            return $entry->is_valid;
        }

        return false;
    }

    /**
     * Check if faculty has submitted late
     * 
     * @param string $period Grading period
     * 
     * @return boolean
     */
    public function isSubmittedLate($period = null)
    {
        if ($period === null) {
            $period = Settings::get('period', 'prelim');
        }

        $period = strtoupper($period);
        $submissions = $this->submissionLogs->groupBy('period')->sortByDesc('date');

        if ($entries = $submissions->get($period)) {
            $deadline = Settings::getCurrentDeadline($period);
            $latest = $entries->first();

            if (!$deadline || $deadline == 'N/A') {
                return false;
            }

            return strtotime($deadline) < strtotime($latest->getOriginal('date'));
        }

        return false;
    }

    /**
     * Get the number of failed students
     * 
     * @param string $period Grading period
     * 
     * @return integer
     */
    public function getFailedCount($period = null)
    {
        if ($period === null) {
            $period = Settings::get('period', 'prelim');
        }

        $period = strtolower($period) . '_grade';
        $count = 0;

        foreach ($this->submittedGrades as $grade) {
            if ($grade->getOriginal($period) !== null && $grade->getOriginal($period) >= 5.0) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get the number of dropped students
     * 
     * @param string $period Period
     * 
     * @return integer
     */
    public function getDroppedCount($period = null)
    {
        if ($period === null) {
            $period = Settings::get('period', 'prelim');
        }
        
        $period = strtolower($period) . '_grade';
        $count = 0;

        foreach ($this->submittedGrades as $grade) {
            if ($grade->getOriginal($period) !== null && $grade->getOriginal($period) == -1) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Add submission log entry
     */
    public function addSubmissionLogEntry(SgrReporter $report)
    {
        GradeImportLog::create([
            'faculty_id'    => $this->id,
            'period'        => Settings::get('period', 'PRELIM'),
            'date'          => date('Y-m-d H:i:s'),
            'subject'       => $report->getSubject(),
            'section'       => $report->getSection(),
            'is_valid'      => $report->isValid()
        ]);
    }
}
