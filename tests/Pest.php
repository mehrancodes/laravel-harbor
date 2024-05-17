<?php

use App\Services\Forge\ForgeService;
use App\Services\Forge\ForgeSetting;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Server;
use Laravel\Forge\Resources\Site;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function configureMockService(array $settings = [], array $site_attributes = [], array $server_attributes = []): ForgeService
{
    $setting = Mockery::mock(ForgeSetting::class);
    $setting->timeoutSeconds = 0;
    foreach ($settings as $name => $value) {
        $setting->{$name} = $value;
    }

    $forge = Mockery::mock(Forge::class);
    $forge->shouldReceive('setTimeout')
        ->with($setting->timeoutSeconds);

    $service = Mockery::mock(ForgeService::class, [$setting, $forge])->makePartial();
    $service->site = new Site($site_attributes);
    $service->server = new Server($server_attributes);

    return $service;
}
