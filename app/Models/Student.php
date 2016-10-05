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

use Hash;
use App\Extensions\User\Roles\MultiRoleModelInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HashablePasswordTrait;
use App\Traits\ConcatenateNameTrait;
use App\Traits\SearchableTrait;

/**
 * Student model
 * 
 * Model class for students table
 */
class Student extends Model implements Authenticatable, MultiRoleModelInterface
{
    use ConcatenateNameTrait, HashablePasswordTrait,SearchableTrait;
    
    public $incrementing = false;

    protected $fillable = [
        'id',
        'password',
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
    ];

    protected $hidden = ['password'];

    protected $appends = [
        'is_required_info_filled',
        'name'
    ];

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return 'student:' . $this->attributes['id'];
    }

    public function getAuthPassword()
    {
        return $this->attributes['middle_name'];
    }

    public function getRememberToken() {}

    public function setRememberToken($value) {}

    public function getRememberTokenName() {}

    public function getRedirectRoute()
    {
        return 'student.index';
    }

    public function getRole()
    {
        return 'student';
    }

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
         $grades = Grade::where('student_id', $this->id)->get();

         return $grades->map(function (Grade $item) {
            return $item->subject;
         })->toArray();
    }

    /**
     * Get payment status
     * 
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function payment()
    {
        return $this->hasOne(StudentStatus::class);
    }

    /**
     * Check if required informations are filled
     * 
     * @return boolean
     */
    public function getIsRequiredInfoFilledAttribute()
    {
        return $this->mobile_number && $this->email_address && $this->address && 
                $this->guardian_name && $this->guardian_contact_number;
    }

    /**
     * Update student grades
     * 
     * @param array $date Array of grade data
     * 
     * @return boolean
     */
    public function updateGrades(array $data)
    {
        foreach ($data as $row) {
            $query = [
                'student_id' => $this->id,
                'subject'    => $row['subject'],
            ];

            $updatedRecord = [
                'prelim_grade'      => parseGrade($row['prelim_grade']),
                'midterm_grade'     => parseGrade($row['midterm_grade']),
                'prefinal_grade'    => parseGrade($row['prefinal_grade']),
                'final_grade'       => parseGrade($row['final_grade'])
            ];

            Grade::where($query)->update($updatedRecord);
        }
    }

    /**
     * Update payment statusi for student
     * 
     * @param array $values Array of each period with its corresponding value
     */
    public function updatePayment(array $values)
    {
        if ($this->payment === null) {
            $this->payment()->save(new StudentStatus($values));

            return;
        }

        $this->payment->fill($values);
        $this->payment->save();
    }
}
