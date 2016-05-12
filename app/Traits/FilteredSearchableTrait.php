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
trait FilteredSearchableTrait
{
    /**
     * Search
     * 
     * @param string|int $id    ID query
     * @param string     $name  Name query
     * 
     * @param array
     */
    public static function filteredSearch($id = null, $name = null, $importerId = null)
    {
        $concats = array(
            "CONCAT(last_name, ' ', first_name, ' ', middle_name)",
            "CONCAT(last_name, ', ', first_name, ' ', middle_name)",
            "CONCAT(first_name, ' ', middle_name, ' ', last_name)",
            "CONCAT(first_name, ' ', last_name)"
        );

        $result = self::leftJoin('grades', 'students.id', '=', 'grades.student_id')
            ->where('importer_id', $importerId)
            ->orderBy('last_name', 'ASC')
            ->orderBy('first_name', 'ASC')
            ->orderBy('middle_name', 'ASC');

        if (property_exists(get_called_class(), 'searchWithRelations')) {
            $result->with(self::$searchWithRelations);
        }

        if ($id) {
            $result->where('id', 'LIKE', '%' . $id . '%');
        }

        if ($name) {
            $result->where(function (Builder $query) use ($concats, $name) {
                foreach ($concats as $concat) {
                    $query->orWhere(DB::raw($concat), 'LIKE', '%' . $name . '%');
                }
            });
        }

        return $result->paginate(50);
    }
}
