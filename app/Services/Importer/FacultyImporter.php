<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Services\Importer;

use App\Models\Faculty;
use App\Models\Department;
use App\Services\Parser;
use App\Services\Helper;
use App\Services\Hash;

/**
 * Faculty Sheet importer
 */
class FacultyImporter implements ImporterInterface
{
    public static function import($sheets)
    {
        $input = array();
        $mappings = array(
            'BM'    => 'Business Management',
            'BMAT'  => 'Business Management',
            'IT'    => 'Information Technology',
            'ICT'   => 'Information Technology',
            'AT'    => 'Accounting Technology',
            'HR'    => 'Hotel and Restaurant Management',
            'TM'    => 'Hotel and Restaurant Management',
            'TH'    => 'Hotel and Restaurant Management',
            'HR/TM' => 'Hotel and Restaurant Management'
        );

        $departments = Department::all()->groupBy(function (Department $department) {
            return $department->name;
        });

        foreach ($sheets as $sheet) {
            foreach ($sheet as $data) {
                $departmentId = null;

                if (array_key_exists($data['department'], $mappings) &&
                    $departments->has($mappings[$data['department']])) {
                    
                    $departmentId = $departments->get($mappings[$data['department']])[0]->id;
                }

                $input[] = array(
                    'last_name' => $data['last_name'],
                    'first_name'    => $data['first_name'],
                    'middle_name'   => $data['middle_name'],
                    'department_id' => $departmentId,
                    'username'      => strtoupper(substr($data['first_name'], 0, 1) . $data['last_name']),
                    'password'      => Hash::make('stinova123'),
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s')
                );
            }
        }

        Faculty::insert($input);
    }
}