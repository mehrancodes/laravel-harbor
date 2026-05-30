<?php

use App\Services\Forge\ForgeService;
use App\Services\Forge\Api\ForgeClient;
use App\Services\Forge\Data\ForgeSiteData;
use App\Services\Forge\ForgeSetting;

test('it gets the site link using the environment URL when explicitly provided', function () {

    $setting = Mockery::mock(ForgeSetting::class);
    $setting->environmentUrl = 'https://foo.bar';

    $service = new ForgeService($setting, Mockery::mock(ForgeClient::class));

    expect($service->getSiteLink())->toBe('https://foo.bar');
});

test('it gets the site link using HTTPS when SSL is required', function () {

    $setting = Mockery::mock(ForgeSetting::class);
    $setting->environmentUrl = null;
    $setting->sslRequired = true;

    $service = new ForgeService($setting, Mockery::mock(ForgeClient::class));

    $site = new ForgeSiteData(
        id: 1,
        name: 'foo.bar',
        status: null,
        username: 'forge',
        repository: null,
        webDirectory: '/home/forge/foo.bar/public',
        rootDirectory: '/home/forge/foo.bar',
        directory: '/public',
        deploymentUrl: null,
    );
    $service->setSite($site);

    expect($service->getSiteLink())->toBe('https://foo.bar');
});

test('it gets the site link using HTTP when SSL is not required', function () {

    $setting = Mockery::mock(ForgeSetting::class);
    $setting->environmentUrl = null;
    $setting->sslRequired = false;

    $service = new ForgeService($setting, Mockery::mock(ForgeClient::class));

    $site = new ForgeSiteData(
        id: 1,
        name: 'foo.bar',
        status: null,
        username: 'forge',
        repository: null,
        webDirectory: '/home/forge/foo.bar/public',
        rootDirectory: '/home/forge/foo.bar',
        directory: '/public',
        deploymentUrl: null,
    );
    $service->setSite($site);

    expect($service->getSiteLink())->toBe('http://foo.bar');
});

test('it gets the site aliases from settings', function () {

    $setting = Mockery::mock(ForgeSetting::class);
    $setting->subdomainName = 'test-domain';
    $setting->aliases = 'alias1.com, alias2.de, alias3.se';

    $service = new ForgeService($setting, Mockery::mock(ForgeClient::class));

    expect($service->getFormattedAliases())
        ->toBeArray()
        ->toBe([
            'test-domain.alias1.com',
            'test-domain.alias2.de',
            'test-domain.alias3.se',
        ]);
});

test('it handles empty aliases string correctly', function () {
    $setting = Mockery::mock(ForgeSetting::class);
    $setting->subdomainName = 'test-domain';
    $setting->aliases = '';

    $service = new ForgeService($setting, Mockery::mock(ForgeClient::class));

    expect($service->getFormattedAliases())
        ->toBeArray()
        ->toBe([]);
});
