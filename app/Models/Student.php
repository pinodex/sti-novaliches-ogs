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
use App\Traits\ConcatenateNameTrait;
use App\Traits\SearchableTrait;

/**
 * Student model
 * 
 * Model class for students table
 */
class Student extends Model
{
    use ConcatenateNameTrait, SearchableTrait;
    
    public $incrementing = false;

    protected $fillable = array(
        'id',
        'last_name',
        'first_name',
        'middle_name',
        'course',
        'mobile_number',
        'landline',
        'email_address',
        'address',
        'guardian_name',
        'guardian_contact_number',
        'other_info',
        'remarks'
    );

    protected $appends = array(
        'is_required_info_filled',
        'name'
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

         return $grades->map(function (Grade $item) {
            return $item->subject;
         })->toArray();
    }

    /**
     * Check if required informations are filled
     * 
     * @return boolean
     */
    public function getIsRequiredInfoFilledAttribute()
    {
        return $this->mobile_number || $this->email_address || $this->address || 
                $this->guardian_name || $this->guardian_contact_number;
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

            if ($grade = Grade::where($query)->first()) {
                $grade->fill($row);
                $grade->save();
            }
        }
    }
}
