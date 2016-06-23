<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    'student' => [
        App\Http\Controllers\Student\MainController::class
    ],

    'admin' => [
        App\Http\Controllers\Dashboard\AdminController::class,
        App\Http\Controllers\Dashboard\DepartmentController::class,
        App\Http\Controllers\Dashboard\FacultyController::class,
        App\Http\Controllers\Dashboard\GradeCompareController::class,
        App\Http\Controllers\Dashboard\GradeController::class,
        App\Http\Controllers\Dashboard\GuidanceController::class,
        App\Http\Controllers\Dashboard\HeadController::class,
        App\Http\Controllers\Dashboard\MainController::class,
        App\Http\Controllers\Dashboard\MemoController::class,
        App\Http\Controllers\Dashboard\SectionController::class,
        App\Http\Controllers\Dashboard\SettingController::class,
        App\Http\Controllers\Dashboard\StudentController::class
    ],

    'head' => [
        App\Http\Controllers\Dashboard\MainController::class,
        App\Http\Controllers\Dashboard\DepartmentController::class => ['self', 'view'],
        App\Http\Controllers\Dashboard\FacultyController::class => ['view'],
        App\Http\Controllers\Dashboard\StudentController::class => ['index', 'view'],
        App\Http\Controllers\Dashboard\SectionController::class
    ],

    'faculty' => [
        App\Http\Controllers\Dashboard\MainController::class,
        App\Http\Controllers\Dashboard\StudentController::class => ['index', 'view'],
        App\Http\Controllers\Dashboard\Import\GradeImportController::class,
        App\Http\Controllers\Dashboard\MemoController::class
    ],

    'guidance' => [
        App\Http\Controllers\Dashboard\MainController::class,
        App\Http\Controllers\Dashboard\StudentController::class => ['index', 'view']
    ]
];
