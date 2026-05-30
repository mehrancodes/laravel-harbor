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

class OrCreateNewSite
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (is_null($service->site)) {
            $this->information('Creating a new site.');

            $site = $service->createSite(
                $service->setting->server,
                $this->gatherSiteData($service)
            );

            $service->setSite($site);
            $this->addAliases($service);
        }

        return $next($service);
    }

    private function gatherSiteData(ForgeService $service): array
    {
        $data = [
            'type' => $service->setting->projectType,
            'domain_mode' => 'custom',
            'name' => $service->getFormattedDomainName(),
            'www_redirect_type' => 'none',
            'allow_wildcard_subdomains' => false,
            'php_version' => $service->setting->phpVersion,
            'web_directory' => '/public',
            'source_control_provider' => $service->setting->gitProvider,
            'repository' => $service->setting->gitProvider !== 'custom' ? $service->setting->repository : $service->setting->repositoryUrl,
            'branch' => $service->setting->branch,
            'push_to_deploy' => $service->setting->quickDeploy,
            'generate_deploy_key' => $service->setting->githubCreateDeployKey,
        ];

        if ($nginxTemplate = $service->setting->nginxTemplate) {
            $this->information('---> Use the specified Nginx template.');

            $data['nginx_template_id'] = (int) $nginxTemplate;
        }

        if ($service->setting->siteIsolationRequired) {
            $this->information('---> Enabling site isolation.');

            $data['is_isolated'] = true;
            $data['isolated_user'] = $service->getSiteIsolationUsername();
        }

        return array_filter($data, static fn ($value) => $value !== null && $value !== '');
    }

    protected function addAliases(ForgeService $service): void
    {
        foreach ($service->getFormattedAliases() as $alias) {
            $this->information(sprintf('---> Adding alias domain: %s', $alias));
            $service->createDomain($alias);
        }
    }
}
