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
 * Faculty Submission Log model
 * 
 * Model class for faculty_submission_log table
 */
class FacultySubmissionLog extends Model
{
    public $timestamps = false;

    protected $fillable = array(
        'faculty_id',
        'date'
    );

    /**
     * Format date attribute
     * 
     * @param string $value Input date
     * 
     * @return string
     */
    public function getDateAttribute($value)
    {
        if (!$value) {
            return 'N/A';
        }

        return date('M d, Y h:i a', strtotime($value));
    }
}
