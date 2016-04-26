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
 * Grade model for grade table
 */
class Grade extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = array(
        'student_id',
        'subject',
        'course',
        'prelim',
        'midterm',
        'prefinal',
        'final'
    );

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
            'student_id' => $studentId,
            'subject' => $subject
        ))->first(array('section'));

        if (!$section) {
            return array();
        }

        $section = $section->toArray()['section'];

        return self::with('student')->where(array(
            'section' => $section,
            'subject' => $subject
        ))->whereNotNull($period)->orderBy($period, 'DESC')->take(5)->get(array(
            $period, 'student_id'
        ))->toArray();
    }

    /**
     * Import data to databas
     * 
     * @param array $data
     */
    public static function import($data)
    {
        foreach ($data as $subject => $sheet) {
            $subject = explode(' ', $subject)[0];

            foreach ($sheet as $row) {
                if (!$student = Student::find($row['student_id'])) {
                    continue;
                }

                // Prefer row for insertion
                unset($row['name']);

                $subjects = array(
                    $subject => $row
                );

                if (!$student->updateGrades($subjects)) {
                    FlashBag::add('page_errors', 
                        'An error occurred while updating student ' . $student->id . '. ' .
                        'Please verify the imported data.'
                    );
                }
            }
        }
    }
}
