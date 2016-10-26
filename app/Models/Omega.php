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
 * Omega model
 * 
 * Model class for omegas table
 */
class Omega extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'subject',
        'section',
        'prelim_grade',
        'midterm_grade',
        'prefinal_grade',
        'final_grade'
    ];

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
     * final_value attribute mutator
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
}
