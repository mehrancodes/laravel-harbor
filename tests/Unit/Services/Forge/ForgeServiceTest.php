<?php

use App\Services\Forge\ForgeService;
use App\Services\Forge\ForgeSetting;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Site;

test('it gets the site link using the environment URL when explicity provided', function () {

    $setting = Mockery::mock(ForgeSetting::class);
    $setting->environmentUrl = 'https://foo.bar';
    $setting->timeoutSeconds = 0;

    $service = new ForgeService($setting,  new Forge);

    expect($service->getSiteLink())->toBe('https://foo.bar');
});

test('it gets the site link using HTTPS when SSL is required', function () {

    $setting = Mockery::mock(ForgeSetting::class);
    $setting->environmentUrl = null;
    $setting->sslRequired = true;
    $setting->timeoutSeconds = 0;

    $service = new ForgeService($setting,  new Forge);

    $site = mock(Site::class);
    $site->name = 'foo.bar';
    $service->setSite($site);

    expect($service->getSiteLink())->toBe('https://foo.bar');
});

test('it gets the site link using HTTP when SSL is not required', function () {

    $setting = Mockery::mock(ForgeSetting::class);
    $setting->environmentUrl = null;
    $setting->sslRequired = false;
    $setting->timeoutSeconds = 0;

    $service = new ForgeService($setting,  new Forge);

    $site = mock(Site::class);
    $site->name = 'foo.bar';
    $service->setSite($site);

    expect($service->getSiteLink())->toBe('http://foo.bar');
});

test('it gets the site aliases from settings', function () {

    $setting = Mockery::mock(ForgeSetting::class);
    $setting->subdomainName = 'test-domain';
    $setting->aliases = 'alias1.com, alias2.de, alias3.se';
    $setting->timeoutSeconds = 0;

    $service = new ForgeService($setting,  new Forge);

    expect($service->getFormattedAliases())
        ->toBeArray()
        ->toBe([
            'test-domain.alias1.com',
            'test-domain.alias2.de',
            'test-domain.alias3.se',
        ]);
});
