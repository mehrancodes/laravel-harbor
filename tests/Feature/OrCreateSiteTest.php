<?php

use App\Services\Forge\ForgeService;
use App\Services\Forge\ForgeSetting;
use App\Services\Forge\Api\Exceptions\ForgeValidationException as ValidationException;
use App\Services\Forge\Pipeline\OrCreateNewSite;

test('it fails on incorrect payload', function ($site, $expectedErrors) {
    $service = mock(ForgeService::class);
    $setting = Mockery::mock(ForgeSetting::class);
    $setting->server = '111111';
    $setting->projectType = 'php';
    $setting->phpVersion = 'php82';
    $setting->gitProvider = 'github';
    $setting->repository = 'acme/example';
    $setting->repositoryUrl = null;
    $setting->branch = 'main';
    $setting->quickDeploy = false;
    $setting->githubCreateDeployKey = false;
    $setting->nginxTemplate = null;
    $setting->siteIsolationRequired = false;
    $service->setting = $setting;

    $service->shouldReceive('getFormattedDomainName')
        ->once()
        ->andReturn($site['name']);

    $service->shouldReceive('createSite')
        ->once()
        ->with('111111', [
            'type' => 'php',
            'domain_mode' => 'custom',
            'name' => $site['name'],
            'www_redirect_type' => 'none',
            'allow_wildcard_subdomains' => false,
            'php_version' => 'php82',
            'web_directory' => '/public',
            'source_control_provider' => 'github',
            'repository' => 'acme/example',
            'branch' => 'main',
            'push_to_deploy' => false,
            'generate_deploy_key' => false,
        ])
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
