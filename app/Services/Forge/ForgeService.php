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

namespace App\Services\Forge;

use App\Actions\FormattedBranchName;
use App\Actions\GenerateDomainName;
use App\Actions\GenerateStandardizedBranchName;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Server;
use Laravel\Forge\Resources\Site;

class ForgeService
{
    /**
     * The Forge server instance.
     */
    public ?Server $server = null;

    /**
     * The Forge site instance.
     */
    public ?Site $site = null;

    /**
     * New database credentials for updating the site's DB environment keys.
     */
    public ?array $database = [];

    /**
     * To check weather the site is created now.
     */
    public bool $siteNewlyMade = false;

    public ?int $deploymentId;

    public function __construct(public ForgeSetting $setting, public Forge $forge)
    {
        $this->forge->setTimeout($this->setting->timeoutSeconds);
    }

    public function setServer(Server $server): void
    {
        $this->server = $server;
    }

    public function setSite(Site $site): void
    {
        $this->site = $site;
    }

    public function setDatabase(array $database): void
    {
        $this->database = $database;
    }

    public function getFormattedBranchName(): string
    {
        return FormattedBranchName::run(
            $this->setting->branch,
            $this->setting->subdomainPattern
        );
    }

    public function getFormattedDomainName(): string
    {
        $subdomain = $this->setting->subdomainName ?? $this->getFormattedBranchName();

        return GenerateDomainName::run(
            $this->setting->domain,
            $subdomain
        );
    }

    public function getStandardizedBranchName(): string
    {
        return GenerateStandardizedBranchName::run(
            $this->getFormattedBranchName()
        );
    }

    public function createSite(string $serverId, array $payload): Site
    {
        $this->setSite(
            $this->forge->createSite($serverId, $payload)
        );

        $this->markSiteAsNewlyMade();

        return $this->site;
    }

    public function findSite(string $serverId): ?Site
    {
        foreach ($this->forge->sites($serverId) as $site) {
            if ($site->name === $this->getFormattedDomainName()) {
                return $site;
            }
        }

        return null;
    }

    public function markSiteAsNewlyMade(): void
    {
        $this->siteNewlyMade = true;
    }
}
