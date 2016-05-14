<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use Silex\Provider;
use Symfony\Component\Debug;
use Symfony\Component\HttpFoundation;
use App\Services\Service;
use App\Providers;
use App\Services;

$app = new App();

require ROOT . 'config/app.php';

ini_set('display_errors', 0);
date_default_timezone_set('Asia/Manila');

Debug\ExceptionHandler::register($app['debug']);
Debug\ErrorHandler::register();

$app->register(new Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/Views'
));

$app->register(new Provider\FormServiceProvider());
$app->register(new Provider\SessionServiceProvider());
$app->register(new Provider\UrlGeneratorServiceProvider());
$app->register(new Provider\ValidatorServiceProvider());

$app->register(new Provider\TranslationServiceProvider(), array(
    'translator.messages' => array()
));

$app->register(new Providers\IlluminateDatabaseServiceProvider());
$app->register(new Providers\TwigExtensionServiceProvider());

if ($app['debug']) {
    $app->register(new Provider\HttpFragmentServiceProvider());
    $app->register(new Provider\ServiceControllerServiceProvider());
    $app->register(new Provider\WebProfilerServiceProvider());
}

/*
 * Initialize connection to database.
 */
$app['database'];

$app['session.storage.handler'] = function () use ($app) {
    return new HttpFoundation\Session\Storage\Handler\PdoSessionHandler(
        $app['database']->connection()->getPdo(), array(),
        $app['session.storage.options']
    );
};

$app['flashbag'] = $app->share(function () use ($app) {
    return $app['session']->getFlashBag();
});

$app['session']->start();

Service::setApplication($app);

require 'auth.php';
require 'routes.php';

return $app;
