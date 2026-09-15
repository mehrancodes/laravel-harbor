<?php

use App\Services\Forge\Data\ForgeSiteCommandData;
use App\Services\Forge\Pipeline\SeedDatabase;

test('it attempts seed when siteNewlyMade is true', function () {
    $service = configureMockService([
        'dbSeed' => true,
    ]);
    $service->siteNewlyMade = true;

    $next = fn () => true;
    $pipe = Mockery::mock(SeedDatabase::class)
        ->makePartial();
    $pipe->shouldReceive('attemptSeed')
        ->once()
        ->andReturn($next());

    expect($pipe($service, $next))->toBe(true);
});

test('it skips seed when siteNewlyMade is false', function () {
    $service = configureMockService([
        'dbSeed' => true,
    ]);
    $service->siteNewlyMade = false;

    $next = fn () => true;
    $pipe = Mockery::mock(SeedDatabase::class)
        ->makePartial();
    $pipe->shouldReceive('attemptSeed')
        ->never();

    expect($pipe($service, $next))->toBe(true);
});

test('it generates seed command without phpVersion', function () {
    $service = configureMockService([
        'dbSeed' => true,
    ]);
    $service->siteNewlyMade = true;

    $pipe = new SeedDatabase;

    expect($pipe->buildImportCommandContent($service))
        ->toBe('php artisan db:seed');
});

test('it generates seed command with phpVersion', function () {
    $service = configureMockService(
        settings: [
            'dbSeed' => true,
        ],
        siteAttributes: [
            'phpVersion' => 'php81',
        ]
    );
    $service->siteNewlyMade = true;

    $pipe = new SeedDatabase;

    expect($pipe->buildImportCommandContent($service))
        ->toBe('php8.1 artisan db:seed');
});

test('it generates seed command with custom seeder', function () {
    $service = configureMockService([
        'dbSeed' => 'FooSeeder',
    ]);
    $service->siteNewlyMade = true;

    $pipe = new SeedDatabase;

    expect($pipe->buildImportCommandContent($service))
        ->toBe('php artisan db:seed --class=FooSeeder');
});

test('it executes seed command with finished response', function () {
    $service = configureMockService(
        settings: [
            'dbSeed' => true,
            'server' => 1,
        ],
        siteAttributes: [
            'id' => 2,
        ]
    );
    $service->siteNewlyMade = true;

    $siteCommand = new ForgeSiteCommandData(
        id: 10,
        serverId: 1,
        siteId: 2,
        command: 'php artisan db:seed',
        status: 'finished',
        output: '',
        exitCode: 0,
    );

    $service->client->shouldReceive('runSiteCommand')
        ->with(1, 2, 'php artisan db:seed')
        ->once()
        ->andReturn($siteCommand);

    $service->shouldReceive('waitForSiteCommand')
        ->with($siteCommand)
        ->once()
        ->andReturn($siteCommand);

    $next = fn () => true;

    $pipe = new SeedDatabase;
    $result = $pipe->attemptSeed(
        $service,
        $next
    );

    expect($result)->toBe(true);
});

test('it executes seed command with failure status', function () {
    $service = configureMockService([
        'dbSeed' => true,
        'server' => 1,
    ]);

    $siteCommand = new ForgeSiteCommandData(
        id: 10,
        serverId: 1,
        siteId: 1,
        command: 'php artisan db:seed',
        status: 'failed',
        output: 'oops',
        exitCode: 1,
    );

    $service->client->shouldReceive('runSiteCommand')
        ->once()
        ->andReturn($siteCommand);

    $service->shouldReceive('waitForSiteCommand')
        ->with($siteCommand)
        ->once()
        ->andReturn($siteCommand);

    $next = fn () => true;

    $pipe = new SeedDatabase;
    $result = $pipe->attemptSeed(
        $service,
        $next
    );

    expect($result)->toBe($next);
});

test('it executes seed command with missing status', function () {
    $service = configureMockService([
        'dbSeed' => true,
        'server' => 1,
    ]);

    $siteCommand = new ForgeSiteCommandData(
        id: 10,
        serverId: 1,
        siteId: 1,
        command: 'php artisan db:seed',
        status: null,
        output: null,
        exitCode: null,
    );

    $service->client->shouldReceive('runSiteCommand')
        ->once()
        ->andReturn($siteCommand);

    $service->shouldReceive('waitForSiteCommand')
        ->with($siteCommand)
        ->once()
        ->andReturn($siteCommand);

    $next = fn () => true;

    $pipe = new SeedDatabase;
    $result = $pipe->attemptSeed(
        $service,
        $next
    );

    expect($result)->toBe($next);
});
