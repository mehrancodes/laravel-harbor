<?php

declare(strict_types=1);

/**
 * This file is part of Harbor CLI.
 *
 * (c) Mehran Rasulian <mehran.rasulian@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace App\Http\Integrations\Forge\Resources;

use App\Actions\FindSite;
use App\Actions\GenerateDomain;
use App\Actions\InstallGitRepository;
use App\Http\Integrations\Forge\Data\SiteData;
use App\Http\Integrations\Forge\Requests\CreateSiteRequest;

class SiteResource extends Resource
{
    public function firstOrCreate(int $serverId): SiteData
    {
        $domain = GenerateDomain::run();

        if ($site = FindSite::run($this->connector, $serverId, $domain)) {
            return $site;
        }

        $site = $this->connector->send(
            new CreateSiteRequest($serverId, $domain)
        )->dtoOrFail();

        return InstallGitRepository::run($this->connector, $serverId, $site->id);
    }
}
