<?php

namespace App\Providers;

use App\Services\Forge\ForgeService;
use App\Services\Forge\ForgeSetting;
use Illuminate\Support\ServiceProvider;
use Laravel\Forge\Forge;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        //
    }

    public function register(): void
    {
        $this->app->singleton(ForgeService::class, function () {
            return new ForgeService(
                $setting = new ForgeSetting(),
                new Forge($setting->token)
            );
        });
    }
}
