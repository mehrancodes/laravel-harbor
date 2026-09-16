<?php

use App\Services\Forge\ForgeSetting;
use App\Services\Git\GitProviderFactory;
use App\Services\Git\GithubProvider;
use App\Services\Git\GitlabProvider;
use App\Services\Git\ManualProvider;

test('it resolves github provider from git_provider', function () {
    $setting = forgeSettingStub();
    $setting->gitProvider = 'github';
    $setting->gitApiProvider = null;
    $setting->gitToken = 'token';

    expect(GitProviderFactory::make($setting))->toBeInstanceOf(GithubProvider::class);
});

test('it resolves gitlab provider from native forge gitlab provider', function () {
    $setting = forgeSettingStub();
    $setting->gitProvider = 'gitlab';
    $setting->gitApiProvider = null;
    $setting->gitToken = 'token';

    expect(GitProviderFactory::make($setting))->toBeInstanceOf(GitlabProvider::class);
});

test('it resolves gitlab provider when custom forge provider uses gitlab api override', function () {
    $setting = forgeSettingStub();
    $setting->gitProvider = 'custom';
    $setting->gitApiProvider = 'gitlab-custom';
    $setting->gitToken = 'token';

    expect(GitProviderFactory::make($setting))->toBeInstanceOf(GitlabProvider::class);
});

test('it falls back to manual provider when git token is missing', function () {
    $setting = forgeSettingStub();
    $setting->gitProvider = 'github';
    $setting->gitApiProvider = null;
    $setting->gitToken = null;

    expect(GitProviderFactory::make($setting))->toBeInstanceOf(ManualProvider::class);
});

function forgeSettingStub(): ForgeSetting
{
    return (new ReflectionClass(ForgeSetting::class))->newInstanceWithoutConstructor();
}
