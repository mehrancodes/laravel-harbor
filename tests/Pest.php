<?php

use App\Services\Forge\Api\ForgeClient;
use App\Services\Forge\Data\ForgeServerData;
use App\Services\Forge\Data\ForgeSiteData;
use App\Services\Forge\ForgeService;
use App\Services\Forge\ForgeSetting;

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

function configureMockService(array $settings = [], array $siteAttributes = [], array $serverAttributes = []): ForgeService
{
    $setting = Mockery::mock(ForgeSetting::class);
    $setting->timeoutSeconds = 0;
    $setting->phpVersion = null;

    foreach ($settings as $name => $value) {
        $setting->{$name} = $value;
    }

    $client = Mockery::mock(ForgeClient::class);

    $service = Mockery::mock(ForgeService::class, [$setting, $client])->makePartial();
    $service->shouldAllowMockingProtectedMethods();
    $service->site = new ForgeSiteData(
        id: $siteAttributes['id'] ?? 1,
        name: $siteAttributes['name'] ?? 'example.test',
        status: $siteAttributes['status'] ?? null,
        username: $siteAttributes['username'] ?? 'forge',
        repository: $siteAttributes['repository'] ?? null,
        webDirectory: $siteAttributes['webDirectory'] ?? null,
        rootDirectory: $siteAttributes['rootDirectory'] ?? null,
        directory: $siteAttributes['directory'] ?? null,
        deploymentUrl: $siteAttributes['deploymentUrl'] ?? null,
        phpVersion: $siteAttributes['phpVersion'] ?? null,
    );
    $service->server = new ForgeServerData(
        id: $serverAttributes['id'] ?? 1,
        name: $serverAttributes['name'] ?? 'server',
        ipAddress: $serverAttributes['ipAddress'] ?? null,
        databaseType: $serverAttributes['databaseType'] ?? null,
    );

    return $service;
}
