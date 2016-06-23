<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Extensions\Symfony\Form\FormValidatorExtension;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $extensions = $this->app['form.extensions'];

        $this->app->bind('form.extensions', function () use ($extensions) {
            $extensions[] = new FormValidatorExtension();

            return $extensions;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (class_exists('Barryvdh\Debugbar\ServiceProvider')) {
            $this->app->register('Barryvdh\Debugbar\ServiceProvider');
        }

        /**
         * "xethron/migrations-generator": "dev-l5"
         * "way/generators": "dev-feature/laravel-five-stable"
        */
        if (class_exists('Way\Generators\GeneratorsServiceProvider')) {
            $this->app->register('Way\Generators\GeneratorsServiceProvider');
        }
        
        if (class_exists('Xethron\MigrationsGenerator\MigrationsGeneratorServiceProvider')) {
            $this->app->register('Xethron\MigrationsGenerator\MigrationsGeneratorServiceProvider');
        }
    }
}
