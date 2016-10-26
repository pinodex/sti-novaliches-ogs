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
use App\Models\Omega;
use App\Models\Student;
use App\Extensions\Spreadsheet\GradeSpreadsheet;
use App\Extensions\Spreadsheet\OmegaSpreadsheet;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Eloquent\Builder;
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
     * @var array Sections defined in SGR
     */
    protected $sections;

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
    public function __construct(GradeSpreadsheet $sgr)
    {
        $contents = $sgr->getParsedContents();

        $this->sections = $contents['metadata']['sections'];
        $this->subject = $contents['metadata']['subject'];

        $this->sgr = Collection::make();

        foreach ($contents['students'] as $item) {
            $outputs = [];

            foreach ($contents['metadata']['sections'] as $section) {
                $output = [
                    'student_id'        => $item['student_id'],
                    'name'              => $item['name'],
                    'subject'           => $contents['metadata']['subject'],
                    'section'           => $section,
                    'prelim_grade'      => $item['prelim_grade'],
                    'midterm_grade'     => $item['midterm_grade'],
                    'prefinal_grade'    => $item['prefinal_grade'],
                    'final_grade'       => $item['final_grade'],
                    'actual_grade'      => $item['actual_grade']
                ];

                $output['id'] = $this->makeIdentificationHash($output);
                $output['hash'] = $this->makeRecordHash($output);

                $outputs[] = $output;
            }

            $this->sgr = $this->sgr->merge($outputs);
        }

        $this->omega = Omega::where(function (Builder $query) use ($contents) {
            $search = [];

            foreach ($contents['students'] as $student) {
                $query->orWhere(function (Builder $orQuery) use ($student, $contents) {
                    $orQuery->where('subject', $contents['metadata']['subject']);
                    $orQuery->whereIn('section', $contents['metadata']['sections']);
                });
            }
        })->with([
            'student' => function (BelongsTo $relation) {
                $relation->select('id', 'last_name', 'first_name', 'middle_name', 'course', 'section');
            }
        ])->get()->transform(function ($item) {
            $output = [
                'student_id'        => $item['student_id'],
                'name'              => $item['student']['name'],
                'subject'           => $item['subject'],
                'section'           => $item['section'],
                'prelim_grade'      => parseGrade($item['prelim_grade']),
                'midterm_grade'     => parseGrade($item['midterm_grade']),
                'prefinal_grade'    => parseGrade($item['prefinal_grade']),
                'final_grade'       => parseGrade($item['final_grade']),
                'actual_grade'      => parseGrade($item['actual_grade'])
            ];

            $output['id'] = $this->makeIdentificationHash($output);
            $output['hash'] = $this->makeRecordHash($output);

            return $output;
        });

        $this->sgr = $this->sgr->filter(function ($item) {
            return $this->omega->search(function ($result) use ($item) {
                return $result['id'] == $item['id'];
            }) !== false;
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
    public static function check(GradeSpreadsheet $sgr)
    {
        return new static($sgr);
    }

    /**
     * Get valid record count
     * 
     * @return int
     */
    public function getValidCount()
    {
        $count = $this->sgr->count() - $this->invalidRecordCount;

        if ($count < 0) {
            $count = 0;
        }

        return $count;
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
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
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

        $this->diff = Collection::make();

        $this->diff = $this->diff->merge($omegaHashes->diff($sgrHashes));
        $this->diff = $this->diff->merge($sgrHashes->diff($omegaHashes));

        $this->diff->transform(function ($hash) use ($sgrHashes, $omegaHashes) {
            $sgr = $this->sgr->first(function ($key, $value) use ($hash) {
                return $value['hash'] == $hash;
            });
            
            if ($sgr) {
                return $sgr;
            }

            $omega = $this->omega->first(function ($key, $value) use ($hash) {
                return $value['hash'] == $hash;
            });

            if ($omega) {
                return $omega;
            }
        });

        $this->diff = $this->diff->unique(function ($item) {
            return $item['id'];
        });

        $this->diff->transform(function ($record) {
            $output = [
                'student_id'    => null,
                'student_name'  => null,
                'sgr'           => null,
                'omega'         => null
            ];

            $sgr = $this->sgr->first(function ($key, $value) use ($record) {
                return $value['id'] == $record['id'];
            });

            $omega = $this->omega->first(function ($key, $value) use ($record) {
                return $value['id'] == $record['id'];
            });

            if ($sgr) {
                $output['student_id'] = $sgr['student_id'];
                $output['student_name'] = $sgr['name'];

                $output['sgr'] = $this->getGrades($sgr);
            }

            if ($omega) {
                $output['student_id'] = $omega['student_id'];
                $output['student_name'] = $omega['name'];

                $output['omega'] = $this->getGrades($omega);
            }

            return $output;
        });

        $this->diff = $this->diff->sortBy('student_name');

        $this->diff->each(function ($item) {
            $others = $this->diff->where('student_id', $item['student_id']);

            if ($others->count() > 1) {
                $others->where('omega', null)->keys()->each(function ($key) {
                    $this->diff->forget($key);
                });
            }
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
        return $this->diff !== null && $this->diff->isEmpty();
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
            $record['final_grade'] .
            $record['actual_grade']
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
            'final_grade'       => $record['final_grade'],
            'actual_grade'      => $record['actual_grade']
        ];
    }
}
