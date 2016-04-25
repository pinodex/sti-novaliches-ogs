<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Providers;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

/**
 * Illuminate Database Service
 * 
 * Used to inject illuminate to container
 */
class IlluminateDatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['database'] = $app->share(function () use ($app) {
            $capsule = new Capsule();

            $capsule->addConnection($app['database.connection']);
            $capsule->setEventDispatcher(new Dispatcher(new Container()));

            if (isset($app['database.connection']['logging']) && 
                $app['database.connection']['logging'] === true) {
                
                $capsule->getConnection()->enableQueryLog();
            }

            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            return $capsule;
        });
    }

    public function boot(Application $app) {}
}
