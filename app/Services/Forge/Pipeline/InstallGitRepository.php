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

class InstallGitRepository
{
    use Outputifier;

    public function __construct(public GithubService $githubService)
    {
        //
    }

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (! $service->siteNewlyMade && ! is_null($service->site->repository)) {
            return $next($service);
        }

        $this->information('Installing the git repository.');

        if ($service->setting->githubCreateDeployKey) {
            $this->information('---> Creating deploy key on Forge.');

            $data = $service->site->createDeployKey();

            $this->information('---> Adding deploy key to GitHub repository.');

            $this->githubService->createDeployKey(
                $service->getDeployKeyTitle(),
                $data['key']
            );
        }

        $service->setSite(
            $service->site->installGitRepository([
                'provider' => $service->setting->gitProvider,
                'repository' => $service->setting->gitProvider !== 'custom' ? $service->setting->repository : $service->setting->repositoryUrl,
                'branch' => $service->setting->branch,
                'composer' => false,
            ])
        );

        return $next($service);
    }
}
