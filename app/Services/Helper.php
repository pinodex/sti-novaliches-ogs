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
    public static function parseId($id)
    {
        if (preg_match('/[\d+]{3}-[\d+]{4}-[\d+]{4}/', $id)) {
            return str_replace('-', '', $id);
        }

        return $id;
    }

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
}
