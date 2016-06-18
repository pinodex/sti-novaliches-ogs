<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions;

use App\Models\Setting as SettingModel;

/**
 * Provides settings service
 */
class Settings
{
    /**
     * @var array Setting values
     */
    private static $values;

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
        if (!self::$values) {
            static::getAll();
        }

        if (array_key_exists($id, self::$values)) {
            return self::$values[$id];
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
        if (self::$values) {
            return self::$values;
        }

        self::$values = array();

        SettingModel::all()->map(function (SettingModel $setting) use (&$settings) {
            self::$values[$setting->id] = $setting->value;
        });

        return self::$values;
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

        self::$values[$id] = $value;
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
    public static function getCurrentDeadline($period = null)
    {
        $settingKey = strtolower($period ?: static::get('period', 'prelim')) . '_grade_deadline';
        
        return static::get($settingKey, null);
    }
}
