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

/**
 * Gives a model method to search by id or name
 */
trait RelationSearchableTrait
{
    /**
     * Search related
     * 
     * @param string     $relation  Relation method
     * @param string|int $id        ID query
     * @param string     $name      Name query
     * 
     * @param array
     */
    public function searchRelated($relation, $id, $name)
    {
        $query = $this->{$relation}()->getQuery();

        $nameFormats = array(
            DB::raw("CONCAT(last_name, ' ', first_name, ' ', middle_name)"),
            DB::raw("CONCAT(last_name, ', ', first_name, ' ', middle_name)"),
            DB::raw("CONCAT(first_name, ' ', middle_name, ' ', last_name)"),
            DB::raw("CONCAT(first_name, ' ', last_name)")
        );

        $result = $query->orderBy('last_name', 'ASC')->orderBy('first_name', 'ASC')->orderBy('middle_name', 'ASC');

        if ($id) {
            $result->where('id', 'LIKE', '%' . $id . '%');
        }

        if ($name) {
            $result->where($nameFormats[0], 'LIKE', '%' . $name . '%')
                ->orWhere($nameFormats[1], 'LIKE', '%' . $name . '%')
                ->orWhere($nameFormats[2], 'LIKE', '%' . $name . '%')
                ->orWhere($nameFormats[3], 'LIKE', '%' . $name . '%');
        }

        return $result->paginate(50);
    }
}
