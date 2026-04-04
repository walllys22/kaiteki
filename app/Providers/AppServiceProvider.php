<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Pagination\Paginator;   
use Illuminate\Support\Facades\URL;
use App\FormFields\Country_codeFormField;
use TCG\Voyager\Facades\Voyager;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Voyager::addFormField(Country_codeFormField::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        // Detectar si estamos detrás de un proxy (como Coolify)
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', true);
        }

        Paginator::useBootstrap();

    }
}
