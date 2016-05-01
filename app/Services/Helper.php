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

/**
 * Helper class
 */
class Helper
{
    /**
     * Parse student ID
     * 
     * @param string $id Student ID
     * 
     * @return string
     */
    public static function parseId($id)
    {
        if (preg_match('/[\d+]{3}-[\d+]{4}-[\d+]{4}/', $id)) {
            return str_replace('-', '', $id);
        }

        return $id;
    }

    /**
     * Check if string is a valid student ID
     * 
     * @param string $iid Student ID
     * 
     * @return boolean
     */
    public static function isStudentId($id)
    {
        /**
         * Pattern definition:
         * 
         * XXX-XXXX-XXXX OR XXXXXXXXXXX
         * where X is a number from 0 to 9.
         * 
         * Example matches:
         *  - 021-2015-0330
         *  - 02120150330
         */
        return preg_match('/([\d+]{3}-[\d+]{4}-[\d+]{4})|([\d+]{3})([\d+]{4})([\d+]{4})/', $id);
    }

    /**
     * Format student ID to XXX-XXXX-XXXX
     * 
     * @param string $id Raw student ID
     * 
     * @return string
     */
    public static function formatStudentId($id)
    {
        $matches = array();
        $match = preg_match_all('/([\d+]{3})([\d+]{4})([\d+]{4})/', $id, $matches);

        if (!$match) {
            return $id;
        }

        return sprintf('%s-%s-%s',
            $matches[1][0],
            $matches[2][0],
            $matches[3][0]
        );
    }

    /**
     * Get display text for grade value
     * 
     * @param string $grade Grade
     * 
     * @return string|int
     */
    public static function formatGrade($grade)
    {
        if ($grade === null || $grade === '') {
            return 'N/A';
        }

        if ($grade === 0 || $grade === 'INC') {
            return 'INC';
        }

        if ($grade === -1 || $grade === 'DRP') {
            return 'DRP';
        }

        return $grade;
    }

    /**
     * Get HTML class for grade value
     * 
     * @param string $grade Grade
     * 
     * @return string
     */
    public static function getGradeClass($grade)
    {
        if ($grade === null || $grade === '') {
            return 'none';
        }

        if ($grade === 0 || $grade === 'INC') {
            return 'incomplete';
        }

        if ($grade === -1 || $grade === 'DRP') {
            return 'incomplete';
        }

        return 'complete';
    }

    /**
     * Format bytes to human-readable format
     * 
     * @param int $bytes Value in bytes
     * 
     * @return string
     */
    public static function formatBytes($bytes)
    {
        if ($bytes == 0) {
            return '0.00 B';
        }

        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        $conv = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $conv), 2) . ' ' . $units[$conv];
    }
}
