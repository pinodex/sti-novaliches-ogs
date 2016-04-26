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

/**
 * Student model
 * 
 * Student model for student table
 */
class Student extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = array(
        'id', 'last_name', 'first_name', 'middle_name', 'course'
    );

    /**
     * Get student grade models
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Get subjects student has enrolled to
     * 
     * @return array
     */
    public function subjects()
    {
        return Grade::where('student_id', $this->id)
            ->get(array('subject'))
            ->pluck('subject')
            ->toArray();
    }

    /**
     * Update student grades
     * 
     * @param array $grades Array of subjects with array of periods
     * 
     * @return boolean
     */
    public function updateGrades($subjects)
    {
        foreach ($subjects as $subject => $grades) {
            $query = array(
                'student_id' => $this->id,
                'subject' => $subject
            );

            if (!array_key_exists('prelim', $grades) &&
                !array_key_exists('midterm', $grades) &&
                !array_key_exists('prefinal', $grades) &&
                !array_key_exists('final', $grades)) {

                return false;
            }

            foreach ($grades as $period => $grade) {
                if (trim($grade) == '') {
                    $grades[$period] = null;

                    continue;
                }

                if (preg_match('/(?i)INC/', $grade)) {
                    $grades[$period] = 0;

                    continue;
                }

                if (!preg_match('/((?![0-5])[0-9]{2,3})|((?i)INC)/', $grade)) {
                    unset($grades[$period]);
                }
            }

            if (Grade::where($query)->first()) {
                Grade::where($query)->update($grades);
            } else {
                $grades['student_id'] = $this->id;
                $grades['subject'] = $subject;

                Grade::create($grades);
            }
        }

        return true;
    }

    /**
     * Search student
     * 
     * @param string $id    Student ID
     * @param string $name  Student name
     * 
     * @param array
     */
    public static function search($id = null, $name = null)
    {
        $nameFormats = array(
            DB::raw("CONCAT(last_name, ' ', first_name, ' ', middle_name)"),
            DB::raw("CONCAT(last_name, ', ', first_name, ' ', middle_name)"),
            DB::raw("CONCAT(first_name, ' ', middle_name, ' ', last_name)"),
            DB::raw("CONCAT(first_name, ' ', last_name)")
        );

        $result = self::orderBy('last_name', 'ASC')
                      ->orderBy('first_name', 'ASC')
                      ->orderBy('middle_name', 'ASC');

        if ($id) {
            $result->where('id', $id);
        }

        if ($name) {
            $result->where($nameFormats[0], 'LIKE', '%' . $name . '%')
                 ->orWhere($nameFormats[1], 'LIKE', '%' . $name . '%')
                 ->orWhere($nameFormats[2], 'LIKE', '%' . $name . '%')
                 ->orWhere($nameFormats[3], 'LIKE', '%' . $name . '%');
        }

        return $result->paginate(50);
    }

    /**
     * Import data to database
     * 
     * @param array $data Array of students
     */
    public static function import($data)
    {
        foreach ($data as $sheet) {
            $chunks = array_chunk($sheet, 500);

            foreach ($chunks as $students) {
                $values = array();
                $bindings = array();

                foreach ($students as $i => $student) {
                    $values[] = '(?,?,?,?,?)';
                    $bindings = array_merge($bindings, array_values($student));
                }

                DB::insert('insert ignore into students values ' . implode(',', $values), $bindings);
            }
        }
    }
}
