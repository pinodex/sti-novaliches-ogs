<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers\Faculty;

use Silex\Application;
use App\Models\Student;
use App\Services\View;
use App\Services\Session\FlashBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Faculty maion controller
 * 
 * Route controllers for /faculty/
 */
class StudentController
{
    /**
     * Faculty student view
     * 
     * URL: /faculty/student/{id}
     */
    public function view(Application $app, $id)
    {
        $student = Student::with('grades')->find($id);

        if (!$student) {
            FlashBag::add('messages', 'danger>>>Student not found');

            return $app->redirect($app->path('faculty.index'));
        }

        return View::render('faculty/students/view', array(
            'student' => $student->toArray(),
            'grades' => $student->grades->toArray()
        ));
    }

    /**
     * Faculty student edit
     * 
     * URL: /faculty/student/{id}/edit
     */
    public function edit(Request $request, Application $app, $id)
    {
        $student = Student::with('grades')->findOrFail($id);
        $grades = $student->grades->toArray();

        if (empty($grades)) {
            FlashBag::add('messages', 'danger>>>Nothing has been imported and can be edited for this student yet.');

            return $app->redirect($app->path('faculty.students.view', array(
                'id' => $id
            )));
        }

        if ($request->getMethod() == 'POST') {
            $subjects = $request->request->get('subjects');
            $student->updateGrades($subjects);

            return $app->redirect($app->path('faculty.students.view', array(
                'id' => $id
            )));
        }

        return View::render('faculty/students/edit', array(
            'student' => $student,
            'grades' => $grades
        ));
    }
}
