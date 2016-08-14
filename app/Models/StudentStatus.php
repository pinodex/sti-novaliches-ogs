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

class StudentStatus extends Model
{
    public $timestamps = false;

    public $incrementing = false;
    
    protected $primaryKey = 'student_id';

    protected $fillable = ['student_id', 'prelim', 'midterm', 'prefinal', 'final'];

    /**
     * Mutate attribute from boolean value to textual
     * 
     * @param string $value Boolean value
     * 
     * @return string
     */
    public function getPrelimAttribute($value)
    {
        return $value == '1' ? 'Paid' : 'Unpaid';
    }

    /**
     * Mutate attribute from boolean value to textual
     * 
     * @param string $value Boolean value
     * 
     * @return string
     */
    public function getMidtermAttribute($value)
    {
        return $value == '1' ? 'Paid' : 'Unpaid';
    }

    /**
     * Mutate attribute from boolean value to textual
     * 
     * @param string $value Boolean value
     * 
     * @return string
     */
    public function getPrefinalAttribute($value)
    {
        return $value == '1' ? 'Paid' : 'Unpaid';
    }

    /**
     * Mutate attribute from boolean value to textual
     * 
     * @param string $value Boolean value
     * 
     * @return string
     */
    public function getFinalAttribute($value)
    {
        return $value == '1' ? 'Paid' : 'Unpaid';
    }

    public function getBooleanValues()
    {
        $values = $this->attributes;

        unset($values['student_id']);

        foreach ($values as $key => $value) {
            $values[$key] = $value == 1;
        }

        return $values;
    }
}
