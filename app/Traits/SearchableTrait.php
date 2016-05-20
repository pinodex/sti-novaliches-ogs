<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Traits;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Builder;

/**
 * Gives a model method to search by id or name
 */
trait SearchableTrait
{
    /**
     * @var array Name concatenation variants
     */
    private static $nameConcats = array(
        "CONCAT(last_name, ' ', first_name, ' ', middle_name)",
        "CONCAT(last_name, ', ', first_name, ' ', middle_name)",
        "CONCAT(first_name, ' ', middle_name, ' ', last_name)",
        "CONCAT(first_name, ' ', last_name)"
    );

    /**
     * Search
     * 
     * @param array $queries Search queries
     * @param array $relations Relations to include to search
     * 
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function search($queries, $relations = null, $builderHook = null)
    {
        $model = static::orderBy(DB::raw(self::$nameConcats[0]), 'ASC');

        if ($relations && count($relations) > 0) {
            call_user_func_array(array($model, 'with'), $relations);
        }

        if ($builderHook) {
            $builderHook($model);
        }

        foreach ($queries as $query) {
            if ($query[0] == 'name') {
                $model->where(function (Builder $builder) use ($query) {
                    foreach (self::$nameConcats as $concat) {
                        $builder->orWhere(DB::raw($concat), $query[1], $query[2]);
                    }
                });

                continue;
            }

            if (is_array($query[1])) {
                $model->whereIn($query[0], $query[1]);

                continue;
            }

            $model->where($query[0], $query[1], $query[2]);
        }

        return $model->paginate(50);
    }
}
