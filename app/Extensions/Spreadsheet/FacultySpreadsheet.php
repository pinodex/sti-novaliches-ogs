<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions\Spreadsheet;

use Hash;
use App\Models\Faculty;
use App\Models\Department;

class FacultySpreadsheet extends AbstractSpreadsheet
{
    /**
     * @var array Mappings of department code to names
     */
    protected $departmentMappings = [
        'BM'    => 'Business Management',
        'BMAT'  => 'Business Management',
        'IT'    => 'Information Technology',
        'ICT'   => 'Information Technology',
        'AT'    => 'Accounting Technology',
        'HR'    => 'Hotel and Restaurant Management',
        'TM'    => 'Hotel and Restaurant Management',
        'TH'    => 'Hotel and Restaurant Management',
        'HR/TM' => 'Hotel and Restaurant Management',
        'GE'    => 'General Education'
    ];

    public function isValid()
    {
        return true;
    }

    public function getParsedContents()
    {
        $contents = [];

        foreach ($this->spreadsheet as $i => $row) {
            if ($i >= 4) {
                if (empty($row[3]) &&
                    empty($row[4]) &&
                    empty($row[5]) &&
                    empty($row[8])) {

                    continue;
                }

                $contents[] = [
                    'last_name' => trim($row[3]),
                    'first_name' => trim($row[4]),
                    'middle_name' => trim($row[5]),
                    'department' => trim($row[8])
                ];
            }
        }

        return $contents;
    }

    public function importToDatabase()
    {
        $departments = Department::all();
        $faculties = Faculty::get(['username', 'first_name', 'middle_name', 'last_name']);

        $entries = [];

        $departments->each(function (Department $department, $i) use ($departments) {
            $departments->put($department->name, $department->id);
            $departments->forget($i);
        });

        $passwordHash = Hash::make('stinova123');

        foreach ($this->getParsedContents() as $entry) {
            $departmentId = null;

            $isMapped = array_key_exists($entry['department'], $this->departmentMappings);
            $hasDepartment = $departments->has($this->departmentMappings[$entry['department']]);

            if ($isMapped && $hasDepartment) {
                $departmentId = $departments->get($this->departmentMappings[$entry['department']]);
            }

            unset($entry['department']);

            $existingKey = $faculties->search(function ($item) use ($entry) {
                return $item->first_name == $entry['first_name'] &&
                    $item->middle_name == $entry['middle_name'] &&
                    $item->last_name == $entry['last_name'];
            });

            if ($existingKey !== false) {
                continue;
            }

            $entry['department_id'] = $departmentId;
            $entry['username'] = $this->generateUsername($entry['first_name'], $entry['last_name']);
            $entry['password'] = $passwordHash;
            $entry['created_at'] = date('Y-m-d H:i:s');
            $entry['updated_at'] = date('Y-m-d H:i:s');

            $entries[] = $entry;
        }

        Faculty::insert($entries);
    }

    /**
     * Generate username based on first name and last name
     * 
     * @param string $firstName First name
     * @param string $lastName Last name
     * 
     * @return string
     */
    protected function generateUsername($firstName, $lastName)
    {
        $firstName = substr($firstName, 0, 1);
        $lastName = explode(',', $lastName)[0];

        return strtoupper(preg_replace('/\s+/', '', $firstName . $lastName));
    }
}
