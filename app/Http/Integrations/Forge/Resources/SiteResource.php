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
use App\Http\Integrations\Forge\Data\SiteData;
use App\Http\Integrations\Forge\Requests\CreateSiteRequest;

class SiteResource extends Resource
{
    public function firstOrCreate(int $serverId): SiteData
    {
        $domain = GenerateDomain::run(
            config('services.forge.domain'),
            config('services.forge.branch')
        );

        if ($site = FindSite::run($this->connector, $serverId, $domain)) {
            return $site;
        }

        return $this->connector->send(
            new CreateSiteRequest($serverId, $domain)
        )->dtoOrFail();
    }
}
