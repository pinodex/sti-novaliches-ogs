<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use App\Routes;
use App\Routes\Student;
use App\Routes\Faculty;
use App\Routes\Admin;
use Symfony\Component\HttpFoundation\JsonResponse;

$app->mount('/', new Routes\MainRoute);

$app->mount('/student', new Student\MainRoute);

$app->mount('/faculty', new Faculty\MainRoute);
$app->mount('/faculty/grades', new Faculty\GradesRoute);
$app->mount('/faculty/student', new Faculty\StudentRoute);

$app->mount('/admin', new Admin\MainRoute);
$app->mount('/admin/manage/admin', new Admin\ManageAdminRoute);
$app->mount('/admin/manage/faculty', new Admin\ManageFacultyRoute);
$app->mount('/admin/manage/student', new Admin\ManageStudentRoute);

$app->error(function (\Exception $e, $code) use ($app) {
    $request = $app['request_stack']->getCurrentRequest();

    // If the request URL starts with /api,
    // send a JSON formatted error
    if (strpos($request->getPathInfo(), '/api') === 0) {
        return new JsonResponse([
            'code' => $code,
            'message' => $e->getMessage()
        ]);
    }
});
