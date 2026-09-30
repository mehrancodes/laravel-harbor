<?php

use App\Services\Forge\Api\ForgeClient;
use App\Services\Forge\Data\ForgeSiteCommandData;
use App\Services\Forge\ForgeSiteCommandWaiter;
use Illuminate\Support\Sleep;

test('it waits until the maximum number of attempts', function () {
    $client = Mockery::mock(ForgeClient::class);
    $siteCommand = new ForgeSiteCommandData(
        id: 1,
        serverId: 1,
        siteId: 2,
        command: 'echo hi',
        status: 'running',
        output: null,
        exitCode: null,
    );

    $waiter = new ForgeSiteCommandWaiter($client);
    $waiter->maxAttempts = 3;
    $waiter->retrySeconds = 5;

    Sleep::fake();

    $client->shouldReceive('getSiteCommand')
        ->times($waiter->maxAttempts)
        ->andReturn($siteCommand);

    $client->shouldReceive('getSiteCommandOutput')
        ->once()
        ->andReturn('');

    $waiter->waitFor($siteCommand);

    Sleep::assertSequence([
        Sleep::for($waiter->retrySeconds)->seconds(),
        Sleep::for($waiter->retrySeconds)->seconds(),
        Sleep::for($waiter->retrySeconds)->seconds(),
    ]);
});

test('it waits until the command is no longer running', function () {
    $client = Mockery::mock(ForgeClient::class);
    $siteCommand = new ForgeSiteCommandData(
        id: 1,
        serverId: 1,
        siteId: 2,
        command: 'echo hi',
        status: 'running',
        output: null,
        exitCode: null,
    );
    $finishedCommand = new ForgeSiteCommandData(
        id: 1,
        serverId: 1,
        siteId: 2,
        command: 'echo hi',
        status: 'finished',
        output: 'done',
        exitCode: 0,
    );

    $waiter = new ForgeSiteCommandWaiter($client);
    $waiter->maxAttempts = 10;
    $waiter->retrySeconds = 5;

    Sleep::fake();

    $client->shouldReceive('getSiteCommand')
        ->times(3)
        ->andReturn(
            $siteCommand,
            $siteCommand,
            $finishedCommand,
        );

    $result = $waiter->waitFor($siteCommand);

    expect($result->status)->toBe($finishedCommand->status);

    Sleep::assertSequence([
        Sleep::for($waiter->retrySeconds)->seconds(),
        Sleep::for($waiter->retrySeconds)->seconds(),
        Sleep::for($waiter->retrySeconds)->seconds(),
    ]);
});
