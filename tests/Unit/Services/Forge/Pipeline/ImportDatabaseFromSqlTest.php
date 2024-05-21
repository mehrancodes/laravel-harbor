<?php

use App\Services\Forge\Pipeline\ImportDatabaseFromSql;
use Laravel\Forge\Resources\SiteCommand;

test('it skips import when dbImportOnDeployment is false', function () {

    $service = configureMockService([
        'dbImportSql' => true,
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

test('it generates import command', function (string $databaseType, string $file, string $expected) {

    $service = configureMockService(
        settings: [
            'dbName' => 'my_db',
        ],
        server_attributes: [
            'databaseType' => $databaseType
        ]
    );
    $service->setDatabase([
        'DB_USERNAME' => 'foo',
        'DB_PASSWORD' => 'bar',
        'DB_HOST' => '1.2.3.4',
        'DB_PORT' => 1234,
    ]);

    $pipe = new ImportDatabaseFromSql();

    expect($pipe->buildImportCommandContent($service, '/path/to/' . $file))
        ->toBe($expected);
})->with([
    'mysql with .gz extension' => ['mysql', 'db.sql.gz', 'gunzip < /path/to/db.sql.gz | mysql -u foo -pbar -h 1.2.3.4 -P 1234 my_db'],
    'mysql with .zip extension' => ['mysql', 'db.sql.zip', 'unzip -p /path/to/db.sql.zip | mysql -u foo -pbar -h 1.2.3.4 -P 1234 my_db'],
    'mysql with .sql extension' => ['mysql', 'db.sql', 'cat /path/to/db.sql | mysql -u foo -pbar -h 1.2.3.4 -P 1234 my_db'],

    'mariadb with .gz extension' => ['mariadb', 'db.sql.gz', 'gunzip < /path/to/db.sql.gz | mariadb -u foo -pbar -h 1.2.3.4 -P 1234 my_db'],
    'mariadb with .zip extension' => ['mariadb', 'db.sql.zip', 'unzip -p /path/to/db.sql.zip | mariadb -u foo -pbar -h 1.2.3.4 -P 1234 my_db'],
    'mariadb with .sql extension' => ['mariadb', 'db.sql', 'cat /path/to/db.sql | mariadb -u foo -pbar -h 1.2.3.4 -P 1234 my_db'],

    'postgres with .gz extension' => ['postgres', 'db.sql.gz', 'gunzip < /path/to/db.sql.gz | pgsql postgres://foo:bar@1.2.3.4:1234/my_db'],
    'postgres with .zip extension' => ['postgres', 'db.sql.zip', 'unzip -p /path/to/db.sql.zip | pgsql postgres://foo:bar@1.2.3.4:1234/my_db'],
    'postgres with .sql extension' => ['postgres', 'db.sql', 'cat /path/to/db.sql | pgsql postgres://foo:bar@1.2.3.4:1234/my_db'],
]);

test('it executes import command with finished response', function () {

    $service = configureMockService(
        settings: [
            'dbName' => 'my_db',
            'server' => 1,
        ],
        site_attributes: [
            'id'     => 2,
        ],
        server_attributes: [
            'databaseType' => 'mysql'
        ]
    );
    $service->setDatabase([
        'DB_USERNAME' => 'foo',
        'DB_PASSWORD' => 'bar',
        'DB_HOST' => '1.2.3.4',
        'DB_PORT' => 1234,
    ]);

    $site_command = Mockery::mock(SiteCommand::class);
    $site_command->status = 'finished';

    $service->forge->shouldReceive('executeSiteCommand')
        ->with(1, 2, ['command' => 'cat x.sql | mysql -u foo -pbar -h 1.2.3.4 -P 1234 my_db'])
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

    $service = configureMockService(
        settings: [
            'dbName' => 'my_db',
            'server' => 1,
        ],
        site_attributes: [
            'id'     => 2,
        ],
        server_attributes: [
            'databaseType' => 'mysql'
        ]
    );

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

    $service = configureMockService(
        settings: [
            'dbName' => 'my_db',
            'server' => 1
        ],
        site_attributes: [
            'id'     => 2,
        ],
        server_attributes: [
            'databaseType' => 'mysql'
        ]
    );

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
