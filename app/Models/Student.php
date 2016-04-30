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
use Illuminate\Database\Capsule\Manager as DB;
use App\Traits\SearchableTrait;

/**
 * Student model
 * 
 * Model class for students table
 */
class Student extends Model
{
    use SearchableTrait;
    
    public $incrementing = false;

    protected $fillable = array(
        'id',
        'last_name',
        'first_name',
        'middle_name',
        'course'
    );

    /**
     * Get student grade models
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function grades()
    {
        return $this->hasMany('App\Models\Grade');
    }

    /**
     * Get subjects student has enrolled to
     * 
     * @return array
     */
    public function subjects()
    {
         $grades = Grade::where('student_id', $this->id)->get();

         return $grades->map(function ($item) {
            return $item->subject;
         })->toArray();
    }

    /**
     * Get user name
     * 
     * @return string
     */
    public function getName()
    {
        return ucwords($this->last_name . ', ' . $this->first_name);
    }

    /**
     * Update student grades
     * 
     * @param array $date Array of grade data
     * 
     * @return boolean
     */
    public function updateGrades($data)
    {
        foreach ($data as $row) {
            $query = array(
                'student_id' => $this->id,
                'subject'    => $row['subject'],
            );

            $grades = array(
                'prelim'    => $row['prelim'],
                'midterm'   => $row['midterm'],
                'prefinal'  => $row['prefinal'],
                'final'     => $row['final'],
            );

            foreach ($grades as $period => $grade) {
                if (trim($grade) == '') {
                    $grades[$period] = null;

                    continue;
                }

                if (preg_match('/(?i)INC/', $grade)) {
                    $grades[$period] = 0;

                    continue;
                }

                if (preg_match('/(?i)DRP/', $grade)) {
                    $grades[$period] = -1;

                    continue;
                }

                if (!preg_match('/((?![0-5])[0-9]{2,3})/', $grade)) {
                    unset($grades[$period]);
                }
            }

            if (Grade::where($query)->first()) {
                Grade::where($query)->update($grades);
            } else {
                Grade::create($row);
            }

        }
    }

    /**
     * Import data to database
     * 
     * @param array $data Array of students
     */
    public static function import($data)
    {
        $timestamp = date('Y-m-d H:i:s');

        foreach ($data as $sheet) {
            $chunks = array_chunk($sheet, 500);

            foreach ($chunks as $students) {
                $values = array();
                $bindings = array();

                foreach ($students as $i => $student) {
                    $values[] = '(?, ?, ?, ?, ?, "' . $timestamp . '", "' . $timestamp . '")';
                    $bindings = array_merge($bindings, array_values($student));
                }

                DB::insert('insert ignore into students values ' . implode(',', $values), $bindings);
            }
        }
    }
}
