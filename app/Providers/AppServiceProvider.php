<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Saloon\Laravel\SaloonServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        //
    }

    public function register(): void
    {
        $this->app->register(SaloonServiceProvider::class);
    }
}
