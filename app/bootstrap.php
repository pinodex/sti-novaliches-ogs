<?php

namespace App;

use Silex\Provider;
use Symfony\Component\Debug;
use App\Services\Service;
use App\Providers;
use App\Services;

$app = new App();

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

require ROOT . 'config/app.php';

Debug\ErrorHandler::register();
Debug\ExceptionHandler::register(
    $app['debug']
);

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

$app['session.storage.handler'] = $app->share(function () use ($app) {
    return new Services\Session\EloquentSessionHandler(
        $app['session.storage.handler.options']
    );
});

$app['flashbag'] = $app->share(function () use ($app) {
    return $app['session']->getFlashBag();
});

$app['session']->start();

Service::setApplication($app);

require 'auth.php';
require 'routes.php';

return $app;
