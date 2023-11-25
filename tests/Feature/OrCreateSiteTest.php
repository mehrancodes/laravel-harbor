<?php

use App\Services\Forge\ForgeService;
use App\Services\Forge\ForgeSetting;
use App\Services\Forge\Pipeline\OrCreateNewSite;
use Laravel\Forge\Exceptions\ValidationException;

test('it fails on incorrect payload', function ($site, $expectedErrors) {
    $service = mock(ForgeService::class);
    $service->setting = new ForgeSetting;
    $service->shouldReceive('getFormattedDomainName')
        ->once()
        ->andReturn($site['name']);

    $service->shouldReceive('getStandardizedBranchName')
        ->once();
    $service->shouldReceive('createSite')
        ->once()
        ->andThrows(ValidationException::class, $expectedErrors);

    expect(
        app(OrCreateNewSite::class)($service, fn ($service) => $service)
    )
        ->toBe($service);
})
    ->with('site', [
        'expected_errors' => [['First Error', 'Second Error']],
    ])
    ->throws(ValidationException::class);
