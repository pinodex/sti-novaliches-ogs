<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Services;

use App\Models\Setting as SettingModel;

/**
 * Provides settings service
 */
class Settings
{
    /**
     * Get setting value
     * 
     * @param string $id Setting entry identifer
     * @param mixed $default Default value if entry was not found
     * 
     * @return string
     */
    public static function get($id, $default = null)
    {
        if ($setting = SettingModel::find($id)) {
            return $setting->value;
        }

        return $default;
    }

    /**
     * Set setting value
     * 
     * @param string $id Setting entry identifer
     * @param string $value Setting entry value
     */
    public function set($id, $value)
    {
        $setting = SettingModel::find($id);

        if (!$setting) {
            $setting = new SettingModel();
        }

        $setting->value = $value;
        $setting->save();
    }
}
