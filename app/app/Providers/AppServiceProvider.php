<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Los links de WhatsApp y los recursos deben salir siempre con https en producción.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
