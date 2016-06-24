<?php

if (!function_exists('parseStudentId')) {
    /**
     * Parse student ID
     * 
     * @param string $id Student ID
     * 
     * @return string
     */
    function parseStudentId($id)
    {
        if (preg_match('/[\d+]{3}-[\d+]{4}-[\d+]{4}/', $id)) {
            return str_replace('-', '', $id);
        }

        return $id;
    }
}

if (!function_exists('isStudentId')) {
    /**
     * Check if string is a valid student ID
     * 
     * @param string $iid Student ID
     * 
     * @return boolean
     */
    function isStudentId($id)
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
}

if (!function_exists('formatStudentId')) {
    /**
     * Format student ID to XXX-XXXX-XXXX
     * 
     * @param string $id Raw student ID
     * 
     * @return string
     */
    function formatStudentId($id)
    {
        $matches = [];
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
}

if (!function_exists('toSchoolEmail')) {
    /**
     * Name to @novaliches.sti.edu email
     * 
     * @param string $firstName First name
     * @param string $lastName Last name
     * 
     * @return string
     */
    function toSchoolEmail($firstName, $lastName)
    {
        $firstName = strtolower(normalizeAccents($firstName));
        $lastName = strtolower(normalizeAccents($lastName));

        $firstName = preg_replace('/\s+/', '', $firstName);
        $lastName = preg_replace('/\s+/', '', $lastName);

        return $firstName . '.' . $lastName . '@novaliches.sti.edu';
    }
}

if (!function_exists('normalizeAccents')) {
    /**
     * Convert accented letters to non-accented one. In layman's terms, enye to just N.
     * Taken from WordPress core.
     * 
     * @param string $string Raw string
     * 
     * @return string
     */
    function normalizeAccents($string)
    {
        if (!preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        $chars = [
            // Decompositions for Latin-1 Supplement
            chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
            chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
            chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
            chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
            chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
            chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
            chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
            chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
            chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
            chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
            chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
            chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
            chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
            chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
            chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
            chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
            chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
            chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
            chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
            chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
            chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
            chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
            chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
            chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
            chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
            chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
            chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
            chr(195).chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
            chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
            chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
            chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
            chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
            chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
            chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
            chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
            chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
            chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
            chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
            chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
            chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
            chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
            chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
            chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
            chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
            chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
            chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
            chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
            chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
            chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
            chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
            chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
            chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
            chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
            chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
            chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
            chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
            chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
            chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
            chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
            chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
            chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
            chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
            chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
            chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
            chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
            chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
            chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
            chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
            chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
            chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
            chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
            chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
            chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
            chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
            chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
            chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
            chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
            chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
            chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
            chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
            chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
            chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
            chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
            chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
            chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
            chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
            chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
            chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
            chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
            chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
            chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
        ];

        return strtr($string, $chars);
    }
}

if (!function_exists('getGradeClass')) {
    /**
     * Get HTML class for grade value
     * 
     * @param string $grade Grade
     * 
     * @return string
     */
    function getGradeClass($grade)
    {
        if ($grade === null || $grade === '' || $grade == 'N/A') {
            return 'color-neutral';
        }

        if ($grade === 0 || $grade === 'INC' || $grade === -1 || $grade === 'DRP' || $grade < 75) {
            return 'color-danger';
        }

        return '';
    }
}

if (!function_exists('parseGrade')) {
    /**
     * Parse grade for database storage
     * 
     * @param string $grade Grade
     * 
     * @return int
     */
    function parseGrade($grade)
    {
        if (trim($grade) == '') {
            return null;
        }

        $grade = strtoupper($grade);

        if ($grade == 'N/A') {
            return null;
        }

        if ($grade == 'INC') {
            return 0;
        }

        if ($grade == 'DRP') {
            return -1;
        }

        if ($grade == 'TRF') {
            return -2;
        }

        return intval($grade);
    }
}

if (!function_exists('formatGrade')) {
    /**
     * Format grade for view
     * 
     * @param int $grade Grade
     * 
     * @return string|int
     */
    function formatGrade($grade)
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

        if ($grade === -2 || $grade === 'TRF') {
            return 'TRF';
        }

        return $grade;
    }
}

if (!function_exists('formatBytes')) {
    /**
     * Format bytes to human-readable format
     * 
     * @param int $bytes Value in bytes
     * 
     * @return string
     */
    function formatBytes($bytes)
    {
        if ($bytes == 0) {
            return '0.00 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $conv = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $conv), 2) . ' ' . $units[$conv];
    }
}

if (!function_exists('settings')) {
    function settings($id, $default = null)
    {
        return \App\Extensions\Settings::get($id, $default);
    }
}
