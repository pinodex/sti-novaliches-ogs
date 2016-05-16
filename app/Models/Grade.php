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

use App\Services\Helper;
use Illuminate\Database\Eloquent\Model;

/**
 * Grade model
 * 
 * Model class for grades table
 */
class Grade extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = array(
        'student_id',
        'importer_id',
        'subject',
        'section',

        'prelim_grade',
        'prelim_attendance_hours',
        'prelim_absent_hours',

        'midterm_grade',
        'midterm_attendance_hours',
        'midterm_absent_hours',

        'prefinal_grade',
        'prefinal_attendance_hours',
        'prefinal_absent_hours',

        'final_grade',
        'final_attendance_hours',
        'final_absent_hours'
    );

    protected $primaryKey = 'student_id';

    /**
     * Get student from grade model
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation;
     */
    public function student()
    {
        return $this->belongsTo('App\Models\Student');
    }

    /**
     * Get grade importer
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation;
     */
    public function importer()
    {
        return $this->belongsTo('App\Models\Faculty', 'importer_id');
    }

    /**
     * prelim_grade attribute accessor
     * 
     * @param int $value Raw value
     * 
     * @return string
     */
    public function getPrelimGradeAttribute($value)
    {
        return Helper::formatGrade($value);
    }

    /**
     * prelim_grade attribute mutator
     * 
     * @param int $value Raw value
     */
    public function setPrelimGradeAttribute($value)
    {
        $this->attributes['prelim_grade'] = Helper::parseGrade($value);
    }

    /**
     * midterm_grade attribute accessor
     * 
     * @param int $value Raw value
     * 
     * @return string
     */
    public function getMidtermGradeAttribute($value)
    {
        return Helper::formatGrade($value);
    }

    /**
     * midterm_value attribute mutator
     * 
     * @param int $value Raw value
     */
    public function setMidtermGradeAttribute($value)
    {
        $this->attributes['midterm_grade'] = Helper::parseGrade($value);
    }

    /**
     * prefinal_grade attribute accessor
     * 
     * @param int $value Raw value
     * 
     * @return string
     */
    public function getPrefinalGradeAttribute($value)
    {
        return Helper::formatGrade($value);
    }

    /**
     * prefinal_grade attribute mutator
     * 
     * @param int $value Raw value
     */
    public function setPrefinalGradeAttribute($value)
    {
        $this->attributes['prefinal_grade'] = Helper::parseGrade($value);
    }

    /**
     * final_grade attribute accessor
     * 
     * @param int $value Raw value
     * 
     * @return string
     */
    public function getFinalGradeAttribute($value)
    {
        return Helper::formatGrade($value);
    }

    /**
     * final_value attribute mutator
     * 
     * @param int $value Raw value
     */
    public function setFinalGradeAttribute($value)
    {
        $this->attributes['final_grade'] = Helper::parseGrade($value);
    }

    /**
     * Get top students
     * 
     * @param string $period    Class period
     * @param string $subject   Subject
     * @param string $studentId Student ID
     * 
     * @return array
     */
    public static function getTopByTermAndSubject($period, $subject, $studentId)
    {
        // Retrieve student section first
        $section = self::where(array(
            'student_id'    => $studentId,
            'subject'       => $subject
        ))->first(array('section'));

        if (!$section) {
            return array();
        }

        $section = $section->toArray()['section'];
        $period = $period . '_grade';

        return self::with('student')
            ->where(array('section' => $section, 'subject' => $subject))
            ->whereNotNull($period)
            ->orderBy($period, 'DESC')
            ->take(5)
            ->get(array($period, 'student_id'))
            ->toArray();
    }
}
