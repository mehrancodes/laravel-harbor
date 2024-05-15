<?php

use App\Services\Forge\ForgeService;
use App\Services\Forge\ForgeSetting;
use App\Services\Forge\Pipeline\ImportDatabaseFromSql;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Site;
use Laravel\Forge\Resources\SiteCommand;

function configureMockService(array $settings = [], array $site_attributes = []): ForgeService
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

    return $service;
}

test('it skips import when dbImportOnDeployment is false', function () {

    $service = configureMockService([
        'dbImportOnDeployment' => false,
    ]);

    $pipe = Mockery::mock(ImportDatabaseFromSql::class)
        ->makePartial();
    $pipe->shouldReceive('attemptImport')
        ->never();

    $next = fn () => true;
    expect($pipe($service, $next))->toBe(true);
});

test('it skips import when siteNewlyMade is true and file is not present', function () {

    $service = configureMockService([
        'dbImportOnDeployment' => false,
        'dbImportSql' => null,
    ]);

    $service->siteNewlyMade = true;

    $pipe = Mockery::mock(ImportDatabaseFromSql::class)
        ->makePartial();
    $pipe->shouldReceive('attemptImport')
        ->never();

    $next = fn () => true;
    expect($pipe($service, $next))->toBe(true);
});

test('it attempts import when siteNewlyMade is false, dbImportOnDeployment is true, and file is present', function () {

    $service = configureMockService([
        'dbImportOnDeployment' => true,
        'dbImportSql' => 'xyz.sql',
    ]);

    $next = fn () => true;
    $pipe = Mockery::mock(ImportDatabaseFromSql::class)
        ->makePartial();
    $pipe->shouldReceive('attemptImport')
        ->once()
        ->andReturn($next());

    expect($pipe($service, $next))->toBe(true);
});

test('it attempts import when siteNewlyMade is true and file is present', function () {

    $service = configureMockService([
        'dbImportOnDeployment' => false,
        'dbImportSql' => 'xyz.sql',
    ]);
    $service->siteNewlyMade = true;

    $next = fn () => true;
    $pipe = Mockery::mock(ImportDatabaseFromSql::class)
        ->makePartial();
    $pipe->shouldReceive('attemptImport')
        ->once()
        ->andReturn($next());

    expect($pipe($service, $next))->toBe(true);
});

test('it generates import command for file with .gz extension', function () {

    $service = configureMockService([
        'dbName' => 'my_db',
    ]);
    $service->setDatabase([
        'DB_USERNAME' => 'foo',
        'DB_PASSWORD' => 'bar',
        'DB_HOST' => '1.2.3.4',
        'DB_PORT' => 1234,
    ]);

    $pipe = new ImportDatabaseFromSql();

    expect($pipe->buildImportCommandContent($service, '/path/to/db.sql.gz'))
        ->toBe('gunzip < /path/to/db.sql.gz | mysql -u foo -pbar -P 1234 -h 1.2.3.4 my_db');
});

test('it generates import command for file with .zip extension', function () {

    $service = configureMockService([
        'dbName' => 'my_db',
    ]);
    $service->setDatabase([
        'DB_USERNAME' => 'foo',
        'DB_PASSWORD' => 'bar',
        'DB_HOST' => '1.2.3.4',
        'DB_PORT' => 1234,
    ]);

    $pipe = new ImportDatabaseFromSql();

    expect($pipe->buildImportCommandContent($service, '/path/to/db.sql.zip'))
        ->toBe('unzip -p /path/to/db.sql.zip | mysql -u foo -pbar -P 1234 -h 1.2.3.4 my_db');
});

test('it generates import command for file with .sql extension', function () {

    $service = configureMockService([
        'dbName' => 'my_db',
    ]);
    $service->setDatabase([
        'DB_USERNAME' => 'foo',
        'DB_PASSWORD' => 'bar',
        'DB_HOST' => '1.2.3.4',
        'DB_PORT' => 1234,
    ]);

    $pipe = new ImportDatabaseFromSql();

    expect($pipe->buildImportCommandContent($service, '/path/to/db.sql'))
        ->toBe('cat /path/to/db.sql | mysql -u foo -pbar -P 1234 -h 1.2.3.4 my_db');
});

test('it executes import command with finished response', function () {

    $service = configureMockService([
        'dbName' => 'my_db',
        'server' => 1,
    ], [
        'id'     => 2,
    ]);
    $service->setDatabase([
        'DB_USERNAME' => 'foo',
        'DB_PASSWORD' => 'bar',
        'DB_HOST' => '1.2.3.4',
        'DB_PORT' => 1234,
    ]);

    $site_command = Mockery::mock(SiteCommand::class);
    $site_command->status = 'finished';

    $service->forge->shouldReceive('executeSiteCommand')
        ->with(1, 2, ['command' => 'cat x.sql | mysql -u foo -pbar -P 1234 -h 1.2.3.4 my_db'])
        ->once()
        ->andReturn($site_command);

    $next = fn () => true;

    $pipe = new ImportDatabaseFromSql();
    $result = $pipe->attemptImport(
        $service,
        $next,
        'x.sql'
    );

    expect($result)->toBe(true);
});

test('it executes import command with failure status', function () {

    $service = configureMockService([
        'dbName' => 'my_db',
        'server' => 1,
    ], [
        'id'     => 2,
    ]);

    $site_command = Mockery::mock(SiteCommand::class);
    $site_command->status = 'failed';
    $site_command->output = 'oops';

    $service->forge->shouldReceive('executeSiteCommand')
        ->once()
        ->andReturn($site_command);

    $next = fn () => true;

    $pipe = new ImportDatabaseFromSql();
    $result = $pipe->attemptImport(
        $service,
        $next,
        'x.sql'
    );

    expect($result)->toBe($next);
});

test('it executes import command with missing status', function () {

    $service = configureMockService([
        'dbName' => 'my_db',
        'server' => 1,
    ], [
        'id'     => 2,
    ]);

    $site_command = Mockery::mock(SiteCommand::class);

    $service->forge->shouldReceive('executeSiteCommand')
        ->once()
        ->andReturn($site_command);

    $service->shouldReceive('waitForSiteCommand')
        ->with($site_command)
        ->andReturn($site_command);

    $next = fn () => true;

    $pipe = new ImportDatabaseFromSql();
    $result = $pipe->attemptImport(
        $service,
        $next,
        'x.sql'
    );

    expect($result)->toBe($next);
});
