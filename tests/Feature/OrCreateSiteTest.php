<?php

use App\Services\Forge\Api\Exceptions\ForgeValidationException as ValidationException;
use App\Services\Forge\Data\ForgeSiteData;
use App\Services\Forge\ForgeService;
use App\Services\Forge\ForgeSetting;
use App\Services\Forge\Pipeline\OrCreateNewSite;

test('it fails on incorrect payload', function ($site, $expectedErrors) {
    $service = mockForgeServiceForSiteCreation(directory: '/public');

    $service->shouldReceive('getFormattedDomainName')
        ->once()
        ->andReturn($site['name']);

    $service->shouldReceive('createSite')
        ->once()
        ->with('111111', expectedSitePayload($site['name'], '/public'))
        ->andThrow(new ValidationException(
            message: implode(PHP_EOL, collect($expectedErrors)->flatten()->all()),
            statusCode: 422,
            errors: collect($expectedErrors)->flatten()->all(),
        ));

    expect(
        app(OrCreateNewSite::class)($service, fn ($service) => $service)
    )
        ->toBe($service);
})
    ->with('site', [
        'expected_errors' => [['First Error', 'Second Error']],
    ])
    ->throws(ValidationException::class);

test('it uses the configured directory as web_directory', function () {
    $service = mockForgeServiceForSiteCreation(directory: '/playground/public');
    $createdSite = fakeCreatedSite();

    $service->shouldReceive('getFormattedDomainName')
        ->once()
        ->andReturn('preview.harbor.test');

    $service->shouldReceive('createSite')
        ->once()
        ->with('111111', expectedSitePayload('preview.harbor.test', '/playground/public'))
        ->andReturn($createdSite);

    $service->shouldReceive('setSite')
        ->once()
        ->with($createdSite);

    $service->shouldReceive('getFormattedAliases')
        ->once()
        ->andReturn([]);

    expect(
        app(OrCreateNewSite::class)($service, fn ($service) => $service)
    )
        ->toBe($service);
});

test('it omits web_directory when directory is null', function () {
    $service = mockForgeServiceForSiteCreation(directory: null);
    $createdSite = fakeCreatedSite();

    $service->shouldReceive('getFormattedDomainName')
        ->once()
        ->andReturn('preview.harbor.test');

    $payload = expectedSitePayload('preview.harbor.test', '/public');
    unset($payload['web_directory']);

    $service->shouldReceive('createSite')
        ->once()
        ->with('111111', $payload)
        ->andReturn($createdSite);

    $service->shouldReceive('setSite')
        ->once()
        ->with($createdSite);

    $service->shouldReceive('getFormattedAliases')
        ->once()
        ->andReturn([]);

    expect(
        app(OrCreateNewSite::class)($service, fn ($service) => $service)
    )
        ->toBe($service);
});

function mockForgeServiceForSiteCreation(?string $directory): ForgeService
{
    $service = mock(ForgeService::class);
    $setting = Mockery::mock(ForgeSetting::class);
    $setting->server = '111111';
    $setting->projectType = 'php';
    $setting->phpVersion = 'php82';
    $setting->directory = $directory;
    $setting->gitProvider = 'github';
    $setting->repository = 'acme/example';
    $setting->repositoryUrl = null;
    $setting->branch = 'main';
    $setting->quickDeploy = false;
    $setting->githubCreateDeployKey = false;
    $setting->nginxTemplate = null;
    $setting->siteIsolationRequired = false;
    $service->setting = $setting;
    $service->site = null;

    return $service;
}

function expectedSitePayload(string $name, string $webDirectory): array
{
    return [
        'type' => 'php',
        'domain_mode' => 'custom',
        'name' => $name,
        'www_redirect_type' => 'none',
        'allow_wildcard_subdomains' => false,
        'php_version' => 'php82',
        'web_directory' => $webDirectory,
        'source_control_provider' => 'github',
        'repository' => 'acme/example',
        'branch' => 'main',
        'push_to_deploy' => false,
        'generate_deploy_key' => false,
    ];
}

function fakeCreatedSite(): ForgeSiteData
{
    return new ForgeSiteData(
        id: 1,
        name: 'preview.harbor.test',
        status: null,
        username: 'forge',
        repository: null,
        webDirectory: null,
        rootDirectory: null,
        directory: null,
        deploymentUrl: null,
    );
}
