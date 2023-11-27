<?php

declare(strict_types=1);

/**
 * This file is part of Veyoze CLI.
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

class CreateGitDeployment
{
    use Outputifier;

    public function __construct(public GithubService $githubService)
    {
    }

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (! $service->setting->gitDeploymentEnabled) {
            return $next($service);
        }

        $deployment = $this->githubService->createDeployment(
            $service->setting->gitToken,
            $service->setting->repository,
            $service->setting->branch
        );

        $service->setDeploymentId($deployment['id']);

        return $next($service);
    }
}
