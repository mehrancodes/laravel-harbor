<?php

use App\Services\Forge\Pipeline\SeedDatabase;
use Laravel\Forge\Resources\SiteCommand;

test('it attempts import when siteNewlyMade is true', function () {

    $service = configureMockService([
        'dbSeed' => true
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

test('it skips import when siteNewlyMade is false', function () {

    $service = configureMockService([
        'dbSeed' => true
    ]);
    $service->siteNewlyMade = false;

    $next = fn () => true;
    $pipe = Mockery::mock(SeedDatabase::class)
        ->makePartial();
    $pipe->shouldReceive('attemptSeed')
        ->never();

    expect($pipe($service, $next))->toBe(true);
});

test('it generates import command without phpVersion', function () {

    $service = configureMockService([
        'dbSeed' => true,
    ]);
    $service->siteNewlyMade = true;

    $pipe = new SeedDatabase();

    expect($pipe->buildImportCommandContent($service))
        ->toBe('php artisan db:seed');
});

test('it generates import command with phpVersion', function () {

    $service = configureMockService([
        'dbSeed' => true,
    ], [
        'phpVersion' => 'php81',
    ]);
    $service->siteNewlyMade = true;

    $pipe = new SeedDatabase();

    expect($pipe->buildImportCommandContent($service))
        ->toBe('php8.1 artisan db:seed');
});

test('it generates import command with custom seeder on provision', function () {

    $service = configureMockService([
        'dbSeed' => 'FooSeeder',
    ]);
    $service->siteNewlyMade = true;

    $pipe = new SeedDatabase();

    expect($pipe->buildImportCommandContent($service))
        ->toBe('php artisan db:seed --class=FooSeeder');
});

test('it generates import command with custom seeder on deployment', function () {

    $service = configureMockService([
        'dbSeed' => 'FooSeeder',
    ]);
    $service->siteNewlyMade = false;

    $pipe = new SeedDatabase();

    expect($pipe->buildImportCommandContent($service))
        ->toBe('php artisan migrate:fresh --seed --seeder=FooSeeder');
});

test('it executes import command with finished response', function () {

    $service = configureMockService([
        'dbSeed' => true,
        'server' => 1,
    ], [
        'id'     => 2,
    ]);
    $service->siteNewlyMade = true;

    $site_command = Mockery::mock(SiteCommand::class);
    $site_command->status = 'finished';

    $service->forge->shouldReceive('executeSiteCommand')
        ->with(1, 2, ['command' => 'php artisan db:seed'])
        ->once()
        ->andReturn($site_command);

    $next = fn () => true;

    $pipe = new SeedDatabase();
    $result = $pipe->attemptSeed(
        $service,
        $next
    );

    expect($result)->toBe(true);
});

test('it executes import command with failure status', function () {

    $service = configureMockService([
        'dbSeed' => true,
        'server' => 1,
    ]);

    $site_command = Mockery::mock(SiteCommand::class);
    $site_command->status = 'failed';
    $site_command->output = 'oops';

    $service->forge->shouldReceive('executeSiteCommand')
        ->once()
        ->andReturn($site_command);

    $next = fn () => true;

    $pipe = new SeedDatabase();
    $result = $pipe->attemptSeed(
        $service,
        $next
    );

    expect($result)->toBe($next);
});

test('it executes import command with missing status', function () {

    $service = configureMockService([
        'dbSeed' => true,
        'server' => 1,
    ]);

    $site_command = Mockery::mock(SiteCommand::class);

    $service->forge->shouldReceive('executeSiteCommand')
        ->once()
        ->andReturn($site_command);

    $service->shouldReceive('waitForSiteCommand')
        ->with($site_command)
        ->andReturn($site_command);

    $next = fn () => true;

    $pipe = new SeedDatabase();
    $result = $pipe->attemptSeed(
        $service,
        $next
    );

    expect($result)->toBe($next);
});
