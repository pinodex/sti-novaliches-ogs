<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Extensions\Symfony\Form\FormValidatorExtension;
use App\Extensions\GoogleClient;
use App\Extensions\Settings;

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

        $this->app->bind('google', function () {
            $client = new GoogleClient();

            $configFile = storage_path('app/client_secret.json');
            $accessToken = Settings::get('google_access_token');
            $refreshToken = Settings::get('google_refresh_token');

            if (file_exists($configFile)) {
                $client->setAuthConfigFile($configFile);
            }

            if ($accessToken) {
                $client->setAccessToken($accessToken);
            }

            if ($client->isAccessTokenExpired() && $refreshToken) {
                $client->refreshToken($refreshToken);

                Settings::set('google_access_token', $client->getAccessToken());
            }

            $client->setAccessType('offline');

            return $client;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
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
