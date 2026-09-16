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

    public static function make(ForgeSetting $setting): GitProvider
    {
        $provider = $setting->gitApiProvider ?: $setting->gitProvider;

        $implementation = self::PROVIDER_MAP[$provider] ?? null;

        if ($implementation === null || blank($setting->gitToken)) {
            return new ManualProvider();
        }

        return new $implementation($setting);
    }
}
