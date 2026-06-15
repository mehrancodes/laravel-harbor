<?php

use App\Services\Forge\Api\ForgeClient;
use App\Services\Forge\Data\ForgeJobData;
use App\Services\Forge\Data\ForgeSiteData;
use App\Services\Forge\ForgeService;
use App\Services\Forge\ForgeSetting;
use App\Services\Forge\Pipeline\EnsureJobScheduled;

test('it skips scheduler creation when same name and command exist', function () {
    $setting = Mockery::mock(ForgeSetting::class);
    $setting->jobSchedulerRequired = true;
    $setting->server = '936639';
    $client = Mockery::mock(ForgeClient::class);

    $service = new ForgeService($setting, $client);
    $service->site = new ForgeSiteData(
        id: 1,
        name: 'new-timefolio-pr-23.timefolio.app',
        status: null,
        username: 'forge',
        repository: null,
        webDirectory: '/home/forge/new-timefolio-pr-23.timefolio.app/public',
        rootDirectory: '/home/forge/new-timefolio-pr-23.timefolio.app',
        directory: '/public',
        deploymentUrl: null,
    );

    $expectedName = 'Harbor scheduler new-timefolio-pr-23.timefolio.app';
    $expectedCommand = 'php /home/forge/new-timefolio-pr-23.timefolio.app/artisan schedule:run';

    $client->shouldReceive('listJobs')
        ->once()
        ->with('936639')
        ->andReturn([
            new ForgeJobData(
                id: 7,
                name: $expectedName,
                command: $expectedCommand,
                user: 'forge',
            ),
        ]);

    $client->shouldNotReceive('createJob');

    $result = (new EnsureJobScheduled())($service, fn ($passedService) => $passedService);

    expect($result)->toBe($service);
});

test('it creates scheduler when same name exists with different command', function () {
    $setting = Mockery::mock(ForgeSetting::class);
    $setting->jobSchedulerRequired = true;
    $setting->server = '936639';
    $client = Mockery::mock(ForgeClient::class);

    $service = new ForgeService($setting, $client);
    $service->site = new ForgeSiteData(
        id: 1,
        name: 'new-timefolio-pr-23.timefolio.app',
        status: null,
        username: 'forge',
        repository: null,
        webDirectory: '/home/forge/new-timefolio-pr-23.timefolio.app/public',
        rootDirectory: '/home/forge/new-timefolio-pr-23.timefolio.app',
        directory: '/public',
        deploymentUrl: null,
    );

    $expectedName = 'Harbor scheduler new-timefolio-pr-23.timefolio.app';
    $expectedCommand = 'php /home/forge/new-timefolio-pr-23.timefolio.app/artisan schedule:run';

    $client->shouldReceive('listJobs')
        ->once()
        ->with('936639')
        ->andReturn([
            new ForgeJobData(
                id: 8,
                name: $expectedName,
                command: 'php /home/forge/legacy.timefolio.app/artisan schedule:run',
                user: 'forge',
            ),
        ]);

    $client->shouldReceive('createJob')
        ->once()
        ->with('936639', [
            'name' => $expectedName,
            'command' => $expectedCommand,
            'frequency' => 'minutely',
            'user' => 'forge',
        ]);

    $result = (new EnsureJobScheduled())($service, fn ($passedService) => $passedService);

    expect($result)->toBe($service);
});

test('it does nothing when scheduler is disabled', function () {
    $setting = Mockery::mock(ForgeSetting::class);
    $setting->jobSchedulerRequired = false;
    $setting->server = '936639';
    $client = Mockery::mock(ForgeClient::class);

    $service = new ForgeService($setting, $client);
    $service->site = new ForgeSiteData(
        id: 1,
        name: 'new-timefolio-pr-23.timefolio.app',
        status: null,
        username: 'forge',
        repository: null,
        webDirectory: '/home/forge/new-timefolio-pr-23.timefolio.app/public',
        rootDirectory: '/home/forge/new-timefolio-pr-23.timefolio.app',
        directory: '/public',
        deploymentUrl: null,
    );

    $client->shouldNotReceive('listJobs');
    $client->shouldNotReceive('createJob');

    $result = (new EnsureJobScheduled())($service, fn ($passedService) => $passedService);

    expect($result)->toBe($service);
});
