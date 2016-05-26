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
    private $csvData;

    private $dbGrades;

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
        if (!($csvData instanceof Collection)) {
            $csvData = Collection::make($csvData);
        }

        $this->csvData = $csvData;
        $this->dbGrades = $dbGrades;
        $this->aggregation = new Collection();

        // Create a temporary table for grades in CSV
        DB::statement('CREATE TEMPORARY TABLE grades_temporary LIKE grades');

        // Insert CSV contents into the temporary table
        $csvData->chunk(500)->each(function (Collection $chunk) {
            $values = array();
            $bindings = array();

            $chunk->each(function ($row) use (&$values, &$bindings) {
                $values[] = '(?, null, ?, ?, ?, ?, ?, ?, null, null, null, null, null, null, null, null)';
                $bindings = array_merge($bindings, array_values($row));
            });

            DB::insert('INSERT IGNORE INTO grades_temporary VALUES ' . implode(',', $values), $bindings);
        });

        // Duplicate the temporary table because we can't reuse a temporary table in one query
        DB::statement('CREATE TEMPORARY TABLE grades_temporary_join LIKE grades');
        DB::statement('INSERT INTO grades_temporary_join SELECT * FROM grades_temporary');

        /*
            Magic explained:

            Hash each row from grades table by computing the SHA-1 hash of concatenated column values,
            do the same on the temporary table and select every hash in grades table that are
            not in the temporary grades table.

            Left join the temporary grades table to the grades table which returns a record like

            "hash"                  => [SHA-1 hash of row]
            "student_id"            => [Student ID]
            "subject"               => [Subject Code]
            "section"               => [Section]
            "target_prelim_grade"   => [Prelim grade from grades table]
            "target_midterm_grade"  => [Midterm grade from grades table]
            "target_prefinal_grade" => [Pre-final grade from grades table]
            "target_final_grade"    => [Final grade from grades table]
            "source_prelim_grade"   => [Prelim grade from temporary grades table]
            "source_midterm_grade"  => [Midterm grade from temporary grades table]
            "source_prefinal_grade" => [Pre-final grade from temporary grades table]
            "source_final_grade"    => [Final grade from temporary grades table]
         */
        $mismatchSearchQuery = <<<EOF
            SELECT
                SHA1(CONCAT(
                    grades.student_id,
                    grades.subject,
                    grades.section,
                    IFNULL(grades.prelim_grade, ""),
                    IFNULL(grades.midterm_grade, ""),
                    IFNULL(grades.prefinal_grade, ""),
                    IFNULL(grades.final_grade, "")
                )) AS target_hash,

                SHA1(CONCAT(
                    grades_temporary_join.student_id,
                    grades_temporary_join.subject,
                    grades_temporary_join.section,
                    IFNULL(grades_temporary_join.prelim_grade, ""),
                    IFNULL(grades_temporary_join.midterm_grade, ""),
                    IFNULL(grades_temporary_join.prefinal_grade, ""),
                    IFNULL(grades_temporary_join.final_grade, "")
                )) AS source_hash,
                
                grades.student_id,
                grades.subject,
                grades.section,
                
                grades.prelim_grade as target_prelim_grade,
                grades.midterm_grade as target_midterm_grade,
                grades.prefinal_grade as target_prefinal_grade,
                grades.final_grade as target_final_grade,

                grades_temporary_join.prelim_grade as source_prelim_grade,
                grades_temporary_join.midterm_grade as source_midterm_grade,
                grades_temporary_join.prefinal_grade as source_prefinal_grade,
                grades_temporary_join.final_grade as source_final_grade

            FROM grades

            LEFT JOIN grades_temporary_join ON
                grades.student_id = grades_temporary_join.student_id AND
                grades.subject    = grades_temporary_join.subject    AND
                grades.section    = grades_temporary_join.section
                
            HAVING target_hash NOT IN (
                SELECT
                    SHA1(CONCAT(
                        student_id,
                        subject,
                        section,
                        IFNULL(prelim_grade, ""),
                        IFNULL(midterm_grade, ""),
                        IFNULL(prefinal_grade, ""),
                        IFNULL(final_grade, "")
                    ))
                 
                    FROM grades_temporary
            )
EOF;

        $this->mismatches = Collection::make(DB::select($mismatchSearchQuery));
    }

    public function getMismatches()
    {
        return $this->mismatches;
    }
}
