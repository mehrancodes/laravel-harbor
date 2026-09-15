<?php

use App\Services\Forge\Data\ForgeSiteCommandData;
use App\Services\Forge\Pipeline\ImportDatabaseFromSql;

test('it skips import when no SQL file is configured', function () {
    $service = configureMockService([
        'dbImportOnDeployment' => true,
        'dbImportSql' => null,
    ]);

    $next = fn () => true;
    $pipe = Mockery::mock(ImportDatabaseFromSql::class)
        ->makePartial();
    $pipe->shouldReceive('attemptImport')
        ->never();

    expect($pipe($service, $next))->toBe(true);
});

test('it skips import when site is not new and import on deployment is disabled', function () {
    $service = configureMockService([
        'dbImportOnDeployment' => false,
        'dbImportSql' => 'xyz.sql',
    ]);
    $service->siteNewlyMade = false;

    $next = fn () => true;
    $pipe = Mockery::mock(ImportDatabaseFromSql::class)
        ->makePartial();
    $pipe->shouldReceive('attemptImport')
        ->never();

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

test('it generates import command', function (string $databaseType, string $file, string $expected) {
    $service = configureMockService(
        settings: [
            'dbName' => 'my_db',
        ],
        serverAttributes: [
            'databaseType' => $databaseType,
        ]
    );
    $service->setDatabase([
        'DB_USERNAME' => 'foo',
        'DB_PASSWORD' => 'bar',
        'DB_HOST' => '1.2.3.4',
        'DB_PORT' => 1234,
    ]);

    $pipe = new ImportDatabaseFromSql;

    expect($pipe->buildImportCommandContent($service, '/path/to/'.$file))
        ->toBe($expected);
})->with([
    'mysql with .gz extension' => ['mysql', 'db.sql.gz', 'gunzip < /path/to/db.sql.gz | mysql -u foo -pbar -h 1.2.3.4 -P 1234 my_db'],
    'mysql with .zip extension' => ['mysql', 'db.sql.zip', 'unzip -p /path/to/db.sql.zip | mysql -u foo -pbar -h 1.2.3.4 -P 1234 my_db'],
    'mysql with .sql extension' => ['mysql', 'db.sql', 'cat /path/to/db.sql | mysql -u foo -pbar -h 1.2.3.4 -P 1234 my_db'],

    'mariadb with .gz extension' => ['mariadb', 'db.sql.gz', 'gunzip < /path/to/db.sql.gz | mariadb -u foo -pbar -h 1.2.3.4 -P 1234 my_db'],
    'mariadb with .zip extension' => ['mariadb', 'db.sql.zip', 'unzip -p /path/to/db.sql.zip | mariadb -u foo -pbar -h 1.2.3.4 -P 1234 my_db'],
    'mariadb with .sql extension' => ['mariadb', 'db.sql', 'cat /path/to/db.sql | mariadb -u foo -pbar -h 1.2.3.4 -P 1234 my_db'],

    'postgres with .gz extension' => ['postgres', 'db.sql.gz', 'gunzip < /path/to/db.sql.gz | psql postgres://foo:bar@1.2.3.4:1234/my_db'],
    'postgres with .zip extension' => ['postgres', 'db.sql.zip', 'unzip -p /path/to/db.sql.zip | psql postgres://foo:bar@1.2.3.4:1234/my_db'],
    'postgres with .sql extension' => ['postgres', 'db.sql', 'cat /path/to/db.sql | psql postgres://foo:bar@1.2.3.4:1234/my_db'],
]);

test('it executes import command with finished response', function () {
    $service = configureMockService(
        settings: [
            'dbName' => 'my_db',
            'server' => 1,
        ],
        siteAttributes: [
            'id' => 2,
        ],
        serverAttributes: [
            'databaseType' => 'mysql',
        ]
    );
    $service->setDatabase([
        'DB_USERNAME' => 'foo',
        'DB_PASSWORD' => 'bar',
        'DB_HOST' => '1.2.3.4',
        'DB_PORT' => 1234,
    ]);

    $siteCommand = new ForgeSiteCommandData(
        id: 10,
        serverId: 1,
        siteId: 2,
        command: 'cat x.sql | mysql -u foo -pbar -h 1.2.3.4 -P 1234 my_db',
        status: 'finished',
        output: '',
        exitCode: 0,
    );

    $service->client->shouldReceive('runSiteCommand')
        ->with(1, 2, 'cat x.sql | mysql -u foo -pbar -h 1.2.3.4 -P 1234 my_db')
        ->once()
        ->andReturn($siteCommand);

    $service->shouldReceive('waitForSiteCommand')
        ->with($siteCommand)
        ->once()
        ->andReturn($siteCommand);

    $next = fn () => true;

    $pipe = new ImportDatabaseFromSql;
    $result = $pipe->attemptImport(
        $service,
        $next,
        'x.sql'
    );

    expect($result)->toBe(true);
});

test('it executes import command with failure status', function () {
    $service = configureMockService(
        settings: [
            'dbName' => 'my_db',
            'server' => 1,
        ],
        siteAttributes: [
            'id' => 2,
        ],
        serverAttributes: [
            'databaseType' => 'mysql',
        ]
    );
    $service->setDatabase([
        'DB_USERNAME' => 'foo',
        'DB_PASSWORD' => 'bar',
    ]);

    $siteCommand = new ForgeSiteCommandData(
        id: 10,
        serverId: 1,
        siteId: 2,
        command: 'import',
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

    $pipe = new ImportDatabaseFromSql;
    $result = $pipe->attemptImport(
        $service,
        $next,
        'x.sql'
    );

    expect($result)->toBe($next);
});

test('it executes import command with missing status', function () {
    $service = configureMockService(
        settings: [
            'dbName' => 'my_db',
            'server' => 1,
        ],
        siteAttributes: [
            'id' => 2,
        ],
        serverAttributes: [
            'databaseType' => 'mysql',
        ]
    );
    $service->setDatabase([
        'DB_USERNAME' => 'foo',
        'DB_PASSWORD' => 'bar',
    ]);

    $siteCommand = new ForgeSiteCommandData(
        id: 10,
        serverId: 1,
        siteId: 2,
        command: 'import',
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

    $pipe = new ImportDatabaseFromSql;
    $result = $pipe->attemptImport(
        $service,
        $next,
        'x.sql'
    );

    expect($result)->toBe($next);
});
