<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Grade;

/**
 * Allows grade comparison from an uploaded CSV to grades in the database
 */
class GradesComparator
{
    private $mismatches;

    private $columns = array(
        'student_id',
        'importer_id',
        'subject',
        'section',
        'prelim_grade',
        'midterm_grade',
        'prefinal_grade',
        'final_grade'
    );

    public function __construct($csvData, Builder $dbGrades)
    {
        $csvGrades = new Collection();
        $csvHashPair = new Collection();

        // Build a hash table and hash pair of CSV contents
        foreach ($csvData as $data) {
            $rowHash = hash('sha1', implode('', $data));
            $idHash = hash('sha1', $data['student_id'] . $data['section'] . $data['subject']);

            $csvGrades->put($rowHash, $data);
            $csvHashPair->put($idHash, $rowHash);
        }

        $hashes = '(\'' . implode('\',\'', $csvGrades->keys()->toArray()) . '\')';

        $this->mismatches = $dbGrades->select(
            DB::raw('SHA1(CONCAT(
                student_id,
                subject,
                section,
                IFNULL(prelim_grade, ""),
                IFNULL(midterm_grade, ""),
                IFNULL(prefinal_grade, ""),
                IFNULL(final_grade, "")
            )) AS hash,' . implode(',', $this->columns))
        )->having('hash', 'NOT IN', DB::raw($hashes))->get();

        $this->mismatches->transform(function (Grade $grade) use ($csvHashPair, $csvGrades) {
            $mismatch = array(
                'student_id'    => $grade->student_id,
                'section'       => $grade->section,
                'subject'       => $grade->subject,

                'student'       => $grade->student->toArray(),
                'importer'      => $grade->importer->toArray(),
                
                'target' => array(
                    'prelim_grade'      => $grade->prelim_grade,
                    'midterm_grade'     => $grade->midterm_grade,
                    'prefinal_grade'    => $grade->prefinal_grade,
                    'final_grade'       => $grade->final_grade
                ),

                'source' => null
            );

            $idHash = hash('sha1', $grade->student_id . $grade->section . $grade->subject);

            if ($csvHashPair->has($idHash)) {
                $mismatch['source'] = $csvGrades->get($csvHashPair->get($idHash));
            }

            return $mismatch;
        });
    }

    public function getMismatches()
    {
        return $this->mismatches;
    }
}
