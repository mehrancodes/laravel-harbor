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

test('it infers the api provider from a custom repository url', function (string $url, string $expected) {
    $setting = forgeSettingStub();
    $setting->gitProvider = 'custom';
    $setting->gitApiProvider = null;
    $setting->repositoryUrl = $url;
    $setting->gitToken = 'token';

    expect(GitProviderFactory::make($setting))->toBeInstanceOf($expected);
})->with([
    'github scp-style' => ['git@github.com:owner/repo.git', GithubProvider::class],
    'github ssh scheme' => ['ssh://git@github.com/owner/repo.git', GithubProvider::class],
    'github https' => ['https://github.com/owner/repo.git', GithubProvider::class],
    'gitlab scp-style' => ['git@gitlab.com:group/project.git', GitlabProvider::class],
    'unknown host' => ['git@git.example.com:group/project.git', ManualProvider::class],
]);

test('it prefers the explicit api provider over the custom repository url', function () {
    $setting = forgeSettingStub();
    $setting->gitProvider = 'custom';
    $setting->gitApiProvider = 'gitlab';
    $setting->repositoryUrl = 'git@github.com:owner/repo.git';
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
