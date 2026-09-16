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
use App\Services\Git\Contracts\GitProvider;
use App\Traits\Outputifier;
use Closure;

class RemoveExistingDeployKey
{
    use Outputifier;

    public function __construct(public GitProvider $gitProvider)
    {
        //
    }

    public function __invoke(ForgeService $service, Closure $next)
    {
        if ($service->setting->deployKey) {
            $this->information(sprintf('---> Removing existing deploy keys on %s repository.', $this->gitProvider->name()));

            $this->gitProvider->deleteDeployKeysByTitle($service->getDeployKeyTitle());
            $service->deleteDeployKey();
        }

        return $next($service);
    }
}
