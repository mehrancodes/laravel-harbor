<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->setLocale('en');

        $this->loadTranslationsFrom(base_path('lang'), 'harbor');
    }

    public function register(): void
    {
        //
    }
}
