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

/**
 * Grade model
 * 
 * Model class for grades table
 */
class Grade extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'importer_id',
        'subject',
        'section',

        'prelim_grade',
        'prelim_presences',
        'prelim_absences',

        'midterm_grade',
        'midterm_presences',
        'midterm_absences',

        'prefinal_grade',
        'prefinal_presences',
        'prefinal_absences',

        'final_grade',
        'final_presences',
        'final_absences',

        'actual_grade'
    ];

    protected $primaryKey = 'student_id';

    /**
     * Get student from grade model
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation;
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get grade importer
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation;
     */
    public function importer()
    {
        return $this->belongsTo(Faculty::class);
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
        return formatGrade($value);
    }

    /**
     * prelim_grade attribute mutator
     * 
     * @param int $value Raw value
     */
    public function setPrelimGradeAttribute($value)
    {
        $this->attributes['prelim_grade'] = parseGrade($value);
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
        return formatGrade($value);
    }

    /**
     * midterm_value attribute mutator
     * 
     * @param int $value Raw value
     */
    public function setMidtermGradeAttribute($value)
    {
        $this->attributes['midterm_grade'] = parseGrade($value);
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
        return formatGrade($value);
    }

    /**
     * prefinal_grade attribute mutator
     * 
     * @param int $value Raw value
     */
    public function setPrefinalGradeAttribute($value)
    {
        $this->attributes['prefinal_grade'] = parseGrade($value);
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
        return formatGrade($value);
    }

    /**
     * final_grade attribute mutator
     * 
     * @param int $value Raw value
     */
    public function setFinalGradeAttribute($value)
    {
        $this->attributes['final_grade'] = parseGrade($value);
    }

    /**
     * actual_grade attribute accessor
     * 
     * @param int $value Raw value
     * 
     * @return string
     */
    public function getActualGradeAttribute($value)
    {
        return formatGrade($value);
    }

    /**
     * actual_grade attribute mutator
     * 
     * @param int $value Raw value
     */
    public function setActualGradeAttribute($value)
    {
        $this->attributes['actual_grade'] = parseGrade($value);
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
        $section = self::where([
            'student_id'    => $studentId,
            'subject'       => $subject
        ])->first(['section']);

        if (!$section) {
            return [];
        }

        $section = $section->toArray()['section'];
        $period = $period . '_grade';

        return self::with('student')
            ->where(['section' => $section, 'subject' => $subject])
            ->whereNotNull($period)
            ->orderBy($period, 'ASC')
            ->take(5)
            ->get([$period, 'student_id']);
    }
}
