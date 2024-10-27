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
use App\Services\Github\GithubService;
use App\Traits\Outputifier;
use Closure;

class RemoveExistingDeployKey
{
    use Outputifier;

    public function __construct(public GithubService $githubService)
    {
        //
    }

    public function __invoke(ForgeService $service, Closure $next)
    {
        if ($service->setting->githubCreateDeployKey) {
            $this->information('---> Removing existing deploy keys on GitHub repository.');

            $this->githubService->deleteAllKeys($service->getDeployKeyTitle());

            $service->site->destroyDeployKey();
        }

        return $next($service);
    }
}
