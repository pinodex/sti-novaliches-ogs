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
     * Get all setting values
     * 
     * @return array
     */
    public static function getAll()
    {
        $settings = array();

        SettingModel::all()->map(function (SettingModel $setting) use (&$settings) {
            $settings[$setting->id] = $setting->value;
        });

        return $settings;
    }

    /**
     * Set setting value
     * 
     * @param string $id Setting entry identifer
     * @param string $value Setting entry value
     */
    public static function set($id, $value)
    {
        $setting = SettingModel::find($id);

        if (!$setting) {
            $setting = new SettingModel();
            $setting->id = $id;
        }

        $setting->value = $value;
        $setting->save();
    }

    /**
     * Set setting values
     * 
     * @param array $data Values
     */
    public static function setArray($data) {
        foreach ($data as $id => $value) {
            static::set($id, $value);
        }
    }

    /**
     * Get current deadline
     * 
     * @return string
     */
    public static function getCurrentDeadline()
    {
        $settingKey = strtolower(static::get('period', 'prelim')) . '_grade_deadline';
        
        return static::get($settingKey, null);
    }
}
