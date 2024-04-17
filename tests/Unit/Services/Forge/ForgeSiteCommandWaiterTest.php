<?php

use App\Services\Forge\ForgeSiteCommandWaiter;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\SiteCommand;
use Illuminate\Support\Sleep;

test('it waits until the maximum number of attempts', function () {

    $forge = Mockery::mock(Forge::class);
    $site_command = Mockery::mock(SiteCommand::class);

    $waiter = new ForgeSiteCommandWaiter($forge);
    $waiter->maxAttempts = 3;
    $waiter->retrySeconds = 5;

    Sleep::fake();

    $forge->shouldReceive('getSiteCommand')
        ->times($waiter->maxAttempts)
        ->andReturn($site_command);

    $site_command = $waiter->waitFor($site_command);

    Sleep::assertSequence([
        Sleep::for($waiter->retrySeconds)->seconds(),
        Sleep::for($waiter->retrySeconds)->seconds(),
        Sleep::for($waiter->retrySeconds)->seconds(),
    ]);
});

test('it waits until the command is no longer running', function() {

    $forge = Mockery::mock(Forge::class);
    $site_command = Mockery::mock(SiteCommand::class);
    $finished_command = Mockery::mock(SiteCommand::class);
    $finished_command->status = 'finished';

    $waiter = new ForgeSiteCommandWaiter($forge);
    $waiter->maxAttempts = 10;
    $waiter->retrySeconds = 5;

    Sleep::fake();

    $forge->shouldReceive('getSiteCommand')
        ->times(3)
        ->andReturn(
            $site_command,
            $site_command,
            $finished_command
        );

    $site_command = $waiter->waitFor($site_command);

    expect($site_command->status)->toBe($finished_command->status);

    Sleep::assertSequence([
        Sleep::for($waiter->retrySeconds)->seconds(),
        Sleep::for($waiter->retrySeconds)->seconds(),
        Sleep::for($waiter->retrySeconds)->seconds(),
    ]);

});