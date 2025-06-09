<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

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
        // Obtener APP_URL y APP_ENV de la configuración
        $appUrl = Config::get('app.url');
        $appEnv = Config::get('app.env');

        // Solo forzar HTTPS si estamos en local y la URL es de ngrok
        if ($appEnv === 'local' && str_contains($appUrl, 'ngrok-free.app')) {
            URL::forceScheme('https');
        }
    }
}
