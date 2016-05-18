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

use Illuminate\Database\Eloquent\Model;

/**
 * Gives a model method to get array of data for form choices
 */
trait ChoosableTrait
{
    /**
     * Get department list for form choices
     * 
     * return array
     */
    public static function getFormChoices()
    {
        $models = self::get();

        $keys = $models->map(function (Model $item) {
            return strval($item->id);
        })->toArray();

        $values = $models->map(function (Model $item) {
            return $item->getChoiceName();
        })->toArray();

        return array_combine($keys, $values);
    }

    /**
     * Get name for choice display.
     * 
     * return string
     */
    public function getChoiceName()
    {
        return $this->name;
    }
}
