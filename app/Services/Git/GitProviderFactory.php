<?php

declare(strict_types=1);

/**
 * This file is part of Laravel Harbor.
 *
 * (c) Mehran Rasulian <mehran.rasulian@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace App\Services\Git;

use App\Services\Forge\ForgeSetting;
use App\Services\Git\Contracts\GitProvider;

class GitProviderFactory
{
    /**
     * Forge's source control providers mapped to the API Harbor should talk to.
     * Providers absent from this list have no Harbor API integration, so they
     * fall back to the manual provider unless an API provider is configured.
     */
    private const PROVIDER_MAP = [
        'github' => GithubProvider::class,
        'gitlab' => GitlabProvider::class,
        'gitlab-custom' => GitlabProvider::class,
    ];

    /**
     * Public hosts whose API can be inferred from a custom repository URL.
     */
    private const HOST_MAP = [
        'github.com' => 'github',
        'gitlab.com' => 'gitlab',
    ];

    public static function make(ForgeSetting $setting): GitProvider
    {
        $provider = $setting->gitApiProvider ?: self::inferProvider($setting);

        $implementation = self::PROVIDER_MAP[$provider] ?? null;

        if ($implementation === null || blank($setting->gitToken)) {
            return new ManualProvider();
        }

        return new $implementation($setting);
    }

    private static function inferProvider(ForgeSetting $setting): string
    {
        if ($setting->gitProvider !== 'custom' || blank($setting->repositoryUrl)) {
            return $setting->gitProvider;
        }

        // Matches git@host:path, ssh://git@host/path and https://host/path.
        if (! preg_match('~^(?:[a-z+]+://)?(?:[^@/]+@)?([^:/]+)~i', $setting->repositoryUrl, $matches)) {
            return $setting->gitProvider;
        }

        return self::HOST_MAP[strtolower($matches[1])] ?? $setting->gitProvider;
    }
}
