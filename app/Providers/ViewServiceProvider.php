<?php

namespace App\Providers;

use Auth;
use App\Components\Menu;
use App\Models\Bulletin;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $twig = app('twig');
        $executed = false;

        view()->composer('*', function ($view) use ($twig, &$executed) {
            if ($executed) {
                return;
            }

            if (Auth::check()) {
                $menu = Menu::process(config('menu'));

                $twig->addGlobal('menu', $menu);
                
                $executed = true;
            }

            if (request()->route()) {
                $twig->addGlobal('current_route', request()->route()->getName());
            }
        }); 
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
