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
use App\Extensions\Spreadsheet\GradeSpreadsheet;

class SgrReporter
{
    const PRELIM_INVALID_QUARTER = '25% or more of prelim grades are blank';

    const PREVIOUS_PERIOD_INCOMPLETE = 'Previous period grades are incomplete';

    /**
     * @var \Illuminate\Support\Collection Grading sheet contents
     */
    protected $sgr;

    /**
     * @var \Illuminate\Support\Collection Student grades
     */
    protected $students;

    /**
     * @var array Sections defined in SGR
     */
    protected $sections;

    /**
     * @var string Subject defined in SGR
     */
    protected $subject;

    /**
     * @var boolean Is SGR valid
     */
    protected $isValid = true;

    /**
     * @var string Reason message for invalidity
     */
    protected $invalidReason;

    /**
     * @var array Columns to check
     */
    private $columns = ['prelim_grade', 'midterm_grade', 'prefinal_grade', 'final_grade'];

    /**
     * Constructs SgrReporter
     * 
     * @param GradeSpreadsheet $sgr Instance of GradeSpreadsheet
     */
    public function __construct(GradeSpreadsheet $sgr)
    {
        $contents = $sgr->getParsedContents();
        
        $this->students = collect($contents['students']);
        $this->subject = $contents['metadata']['subject'];
        $this->sections = $contents['metadata']['sections'];

        $this->validate();
    }

    /**
     * Validate SGR
     */
    public function validate()
    {
        $currentPeriod = Settings::get('period', 'prelim');

        foreach ($this->columns as $i => $column) {
            $columnValues = $this->students->pluck($column);

            if ($i == 0) {
                if ($this->getNullCount($columnValues) / $columnValues->count() >= 0.25) {
                    $this->isValid = false;
                    $this->invalidReason = static::PRELIM_INVALID_QUARTER;
                    
                    return;
                }

                continue;
            }

            if ($columnValues->count() != $this->getNullCount($columnValues)) {
                $previousColumnValues = $this->students->pluck($this->columns[$i - 1]);

                if ($this->getNullCount($previousColumnValues) > 0) {
                    $this->isValid = false;
                    $this->invalidReason = static::PREVIOUS_PERIOD_INCOMPLETE;

                    return;
                }
            }
        }
    }

    /**
     * Static function for class constructor
     * 
     * @param $sgr GradeSpreadsheet Instance of grading sheet
     * 
     * @return SgrReporter
     */
    public static function check(GradeSpreadsheet $sgr)
    {
        return new static($sgr);
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
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Check if records are valid
     * 
     * @return bool
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * Get invalid reason
     *
     * @return string
     */
    public function getInvalidReason()
    {
        return $this->invalidReason;
    }

    /**
     * Get number of null rows from a column
     *
     * @var \Illuminate\Support\Collection $column Column
     *
     * @return int
     */
    private function getNullCount($column)
    {
        $nullCount = 0;

        $column->each(function ($value) use (&$nullCount) {
            if ($value === null) {
                $nullCount++;
            }
        });

        return $nullCount;
    }
}
