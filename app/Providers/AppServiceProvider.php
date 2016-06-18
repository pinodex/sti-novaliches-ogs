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
        //
    }
}
