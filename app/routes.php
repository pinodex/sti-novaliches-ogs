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
use Symfony\Component\HttpFoundation\JsonResponse;

$app->mount('/', new Routes\MainRoute);
$app->mount('/student', new Routes\StudentRoute);
$app->mount('/faculty', new Routes\FacultyRoute);
$app->mount('/admin', new Routes\AdminRoute);

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
