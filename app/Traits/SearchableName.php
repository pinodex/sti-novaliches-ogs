<?php

/*
 * This file is part of the SIS for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Traits;

use DB;
use Illuminate\Database\Eloquent\Builder;

/**
 * Gives a model method to search by id or name
 */
trait SearchableName
{
    /**
     * @var array Name concatenation variants
     */
    private static $nameConcats = [
        "CONCAT(last_name, ' ', first_name, ' ', middle_name)",
        "CONCAT(last_name, ', ', first_name, ' ', middle_name)",
        "CONCAT(first_name, ' ', middle_name, ' ', last_name)",
        "CONCAT(first_name, ' ', last_name)"
    ];

    /**
     * Search
     * 
     * @param \Illuminate\Database\Eloquent\Model $model Model instance
     * @param string $keyword Search keyword
     */
    public static function searchName($model = null, $keyword)
    {
        if ($model == null) {
            $model = new static;
        }

        return $model->where(function (Builder $builder) use ($keyword) {
            foreach (static::$nameConcats as $concat) {
                $builder->orWhere(DB::raw($concat), 'LIKE', '%' . $keyword . '%');
            }
        });
    }
}
