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
use Illuminate\Database\Query\JoinClause;

class SgrReporter
{
    /**
     * @var \Illuminate\Support\Collection Student IDs from grading sheet
     */
    protected $imported;

    /**
     * @var string Section defined in SGR
     */
    protected $section;

    /**
     * @var string Subject defined in SGR
     */
    protected $subject;

    /**
     * @var array Students with no grades
     */
    protected $noGrades = [];

    /**
     * @var array Students not in OMEGA
     */
    protected $noStudents = [];

    /**
     * @var bool
     */
    protected $isNoGradesLoaded = false;

    /**
     * @var bool
     */
    protected $isNoStudentsLoaded = false;

    /**
     * Constructs SgrReporter
     * 
     * @param GradeSpreadsheet $sgr Instance of GradeSpreadsheet
     */
    public function __construct(GradeSpreadsheet $sgr)
    {
        $contents = $sgr->getParsedContents();

        $this->section = $contents['metadata']['section'];
        $this->subject = $contents['metadata']['subject'];

        $this->imported = collect($contents['students']);

        // preload data
        $this->getNoGrades();
        $this->getNoStudents();
    }

    /**
     * Static function for class constructor
     * 
     * @param GradeSpreadsheet Instance of grading sheet
     * 
     * @return SgrReporter
     */
    public static function check(GradeSpreadsheet $sgr)
    {
        return new static($sgr);
    }

    /**
     * Get total grades imported count
     * 
     * @return int
     */
    public function getTotalImports()
    {
        return count($this->imported);
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
     * Get students with no grades
     * 
     * @return array
     */
    public function getNoGrades()
    {
        if ($this->isNoGradesLoaded) {
            return $this->noGrades;
        }

        $ids = $this->imported->pluck('student_id');

        Student::select(['id', 'last_name', 'first_name', 'middle_name'])
            ->where('section', $this->section)
            ->whereNotIn('id', $ids)
            ->each(function (Student $student) {
                $this->noGrades[] = [
                    'id'        => $student->id,
                    'name'      => $student->name,
                    'remark'    => 'No grade'
                ];
            });

        $this->isNoGradesLoaded = true;
        return $this->noGrades;
    }

    /**
     * Get students not in omega
     * 
     * @return array
     */
    public function getNoStudents()
    {
        if ($this->isNoStudentsLoaded) {
            return $this->noStudents;
        }

        Grade::select(['student_id'])
            ->whereNull('id')
            ->leftJoin('students', function (JoinClause $join) {
                $join->on('students.id', '=', 'grades.student_id');
                $join->on('students.section', '=', 'grades.section');
            })
            ->each(function (Grade $grade) use (&$data) {
                $this->noStudents[] = [
                    'id'        => $grade->student_id,
                    'name'      => $this->searchStudentName($grade->student_id),
                    'remark'    => 'Not in OMEGA'
                ];
            });

        $this->isNoStudentsLoaded = true;
        return $this->noStudents;
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
        $index = $this->imported->search(function ($item) use ($id) {
            return $item['student_id'] == $id;
        });

        if ($index === false) {
            return 'N/A';
        }

        return $this->imported[$index]['name'];
    }
}
