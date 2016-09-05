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

use DB;
use App\Models\Grade;
use App\Models\Student;
use App\Extensions\Spreadsheet\GradeSpreadsheet;
use App\Extensions\Spreadsheet\OmegaSpreadsheet;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

class SgrReporter
{
    /**
     * @var \Illuminate\Support\Collection Grading sheet contents
     */
    protected $sgr;

    /**
     * @var \Illuminate\Support\Collection OMEGA sheet contents
     */
    protected $omega;

    /**
     * @var string Section defined in SGR
     */
    protected $section;

    /**
     * @var string Subject defined in SGR
     */
    protected $subject;

    /**
     * @var array Records difference
     */
    protected $diff;

    /**
     * @var int Invalid records count
     */
    protected $invalidRecordCount = 0;

    /**
     * Constructs SgrReporter
     * 
     * @param GradeSpreadsheet $sgr Instance of GradeSpreadsheet
     */
    public function __construct(GradeSpreadsheet $sgr, OmegaSpreadsheet $omega)
    {
        $contents = $sgr->getParsedContents();

        $this->section = $contents['metadata']['section'];
        $this->subject = $contents['metadata']['subject'];

        $this->sgr = Collection::make($contents['students']);
        $this->omega = Collection::make($omega->getParsedContents());

        // Transform to a structure uniform to the structure of OMEGA
        $this->sgr->transform(function ($item) use ($contents) {
            $output = [
                'student_id'        => $item['student_id'],
                'name'              => $item['name'],
                'subject'           => $contents['metadata']['subject'],
                'section'           => $contents['metadata']['section'],
                'prelim_grade'      => $item['prelim_grade'],
                'midterm_grade'     => $item['midterm_grade'],
                'prefinal_grade'    => $item['prefinal_grade'],
                'final_grade'       => $item['final_grade']
            ];

            $output['id'] = $this->makeIdentificationHash($output);
            $output['hash'] = $this->makeRecordHash($output);

            return $output;
        });

        $this->omega->transform(function ($item) {
            $item['id'] = $this->makeIdentificationHash($item);
            $item['hash'] = $this->makeRecordHash($item);

            return $item;
        });

        unset($contents);

        $this->getMismatches();
    }

    /**
     * Static function for class constructor
     * 
     * @param GradeSpreadsheet Instance of grading sheet
     * 
     * @return SgrReporter
     */
    public static function check(GradeSpreadsheet $sgr, OmegaSpreadsheet $omega)
    {
        return new static($sgr, $omega);
    }

    /**
     * Get total grades imported count
     * 
     * @return int
     */
    public function getTotalImports()
    {
        return count($this->sgr);
    }

    /**
     * Get total count of invalid records
     * 
     * @return int
     */
    public function getInvalidRecordCount()
    {
        return $this->invalidRecordCount;
    }

    /**
     * Get subject
     * 
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Get section
     * 
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Get record mismatches
     * 
     * @return array
     */
    public function getMismatches()
    {
        if ($this->diff !== null) {
            return $this->diff;
        }

        $sgrHashes = $this->sgr->pluck('hash');
        $omegaHashes = $this->omega->pluck('hash');

        $sgrIds = $this->sgr->pluck('id');
        $omegaIds = $this->omega->pluck('id');

        $this->diff = $sgrHashes->diff($omegaHashes);

        $this->diff->transform(function ($hash) use ($sgrHashes, $omegaHashes) {
            if ($index = $sgrHashes->search($hash)) {
                return $this->sgr[$index];
            }

            if ($index = $omegaHashes->search($hash)) {
                return $this->omega[$index];
            }
        });

        $this->diff->transform(function ($record) use ($sgrIds, $omegaIds) {
            $output = [
                'student_id'    => null,
                'student_name'  => null,
                'sgr'           => null,
                'omega'         => null
            ];

            if ($sgrIndex = $sgrIds->search($record['id'])) {
                $output['student_id'] = $this->sgr[$sgrIndex]['student_id'];
                $output['student_name'] = $this->sgr[$sgrIndex]['name'];

                $output['sgr'] = $this->getGrades($this->sgr[$sgrIndex]);
            }

            if ($omegaIndex = $omegaIds->search($record['id'])) {
                $output['student_id'] = $this->omega[$omegaIndex]['student_id'];
                $output['student_name'] = $this->omega[$omegaIndex]['name'];

                $output['omega'] = $this->getGrades($this->omega[$omegaIndex]);
            }

            return $output;
        });

        $this->invalidRecordCount = $this->diff->count();

        return $this->diff;
    }

    /**
     * Check if records are valid
     * 
     * @return bool
     */
    public function isValid()
    {
        return $this->diff !== null && empty($this->diff);
    }

    /**
     * Search student name by ID
     * 
     * @param string $id Student ID
     * 
     * @return string
     */
    protected function searchStudentName($id)
    {
        $index = $this->sgr->search(function ($item) use ($id) {
            return $item['student_id'] == $id;
        });

        if ($index === false) {
            return 'N/A';
        }

        return $this->sgr[$index]['name'];
    }

    /**
     * Generate hash for grade record
     * 
     * @param array $record Grade record
     * 
     * @return string
     */
    protected function makeRecordHash($record)
    {
        return hash('sha1',
            $record['student_id'] .
            $record['subject'] .
            $record['section'] .
            $record['prelim_grade'] .
            $record['midterm_grade'] .
            $record['prefinal_grade'] .
            $record['final_grade']
        );
    }

    /**
     * Generate identification hash for grade record
     * 
     * @param array $record Grade record
     * 
     * @return string
     */
    protected function makeIdentificationHash($record)
    {
        return hash('sha1',
            $record['student_id'] .
            $record['subject'] .
            $record['section']
        );
    }

    /**
     * Returns grades from each period from grade entry
     * 
     * @param array $record Grade record
     * 
     * @return array
     */
    protected function getGrades($record)
    {
        return [
            'prelim_grade'      => $record['prelim_grade'],
            'midterm_grade'     => $record['midterm_grade'],
            'prefinal_grade'    => $record['prefinal_grade'],
            'final_grade'       => $record['final_grade']
        ];
    }
}
