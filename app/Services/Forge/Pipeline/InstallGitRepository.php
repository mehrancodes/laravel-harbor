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

/**
 * On Forge's current API, the repository is installed during site creation.
 * This step remains for logging and for existing sites that somehow lack a repo.
 */
class InstallGitRepository
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (! $service->siteNewlyMade && ! is_null($service->site->repository)) {
            return $next($service);
        }

        if (! $service->siteNewlyMade) {
            $this->warning('Site has no repository configured. Automatic repository installation for existing sites is not available on the new Forge API yet.');

            return $next($service);
        }

        if ($service->site->repository) {
            $this->information('Git repository installed during site creation.');
        }

        return $next($service);
    }
}
