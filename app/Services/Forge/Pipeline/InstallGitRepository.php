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

namespace App\Services\Forge\Pipeline;

use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;
use Closure;

class InstallGitRepository
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (! $service->siteNewlyMade) {
            return $next($service);
        }

        $this->information('Installing the git repository.');

        $service->setSite(
            $service->site->installGitRepository([
                'provider' => $service->setting->gitProvider,
                'repository' => $service->setting->repository,
                'branch' => $service->setting->branch,
                'composer' => false,
            ])
        );

        return $next($service);
    }
}
