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

namespace App\Services\Forge;

use App\Actions\FormattedBranchName;
use App\Actions\GenerateDomainName;
use App\Actions\GenerateStandardizedBranchName;
use App\Services\Forge\Api\ForgeClient;
use App\Services\Forge\Data\ForgeDaemonData;
use App\Services\Forge\Data\ForgeDatabaseData;
use App\Services\Forge\Data\ForgeDatabaseUserData;
use App\Services\Forge\Data\ForgeDomainData;
use App\Services\Forge\Data\ForgeJobData;
use App\Services\Forge\Data\ForgeServerData;
use App\Services\Forge\Data\ForgeSiteData;
use Illuminate\Support\Str;
use RuntimeException;

class ForgeService
{
    /**
     * The Forge server instance.
     */
    public ?ForgeServerData $server = null;

    /**
     * The Forge site instance.
     */
    public ?ForgeSiteData $site = null;

    /**
     * New database credentials for updating the site's DB environment keys.
     */
    public ?array $database = [];

    /**
     * To check weather the site is created now.
     */
    public bool $siteNewlyMade = false;

    public function __construct(public ForgeSetting $setting, public ForgeClient $client) {}

    public function setServer(ForgeServerData $server): void
    {
        $this->server = $server;
    }

    public function setSite(ForgeSiteData $site): void
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

    public function getFormattedAliases(): array
    {
        if ($this->setting->aliases === null || $this->setting->aliases === '') {
            return [];
        }

        $subdomain = $this->setting->subdomainName ?? $this->getFormattedBranchName();
        
        return collect(explode(',', $this->setting->aliases))
            ->map(fn($alias) => GenerateDomainName::run(trim($alias), $subdomain))
            ->toArray();
    }

    public function getSiteIsolationUsername(): string
    {
        if (!empty($this->setting->siteIsolationUsername)) {
            return $this->setting->siteIsolationUsername;
        }

        return GenerateStandardizedBranchName::run(
            $this->getFormattedBranchName()
        );
    }

    public function getFormattedDatabaseName(): string
    {
        if ($this->setting->dbName) {
            $dbName = FormattedBranchName::run($this->setting->dbName);
        } else {
            $dbName = $this->getFormattedBranchName();
        }

        return Str::replace('-', '_', $dbName);
    }

    public function getDeployKeyTitle(): string
    {
        return sprintf('Preview deploy key %s', $this->getFormattedDomainName());
    }

    public function siteNginxTemplate(): string
    {
        return $this->client->getSiteNginxConfig($this->setting->server, $this->site->id);
    }

    public function updateSiteNginxTemplate(string $content): void
    {
        $this->client->updateSiteNginxConfig($this->setting->server, $this->site->id, $content);
    }

    public function createSite(string $serverId, array $payload): ForgeSiteData
    {
        $this->setSite(
            $this->waitUntilSiteIsInstalled(
                $this->client->createSite($serverId, $payload)
            )
        );

        $this->markSiteAsNewlyMade();

        return $this->site;
    }

    public function findSite(string $serverId): ?ForgeSiteData
    {
        foreach ($this->client->listSites($serverId) as $site) {
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

    public function getSiteLink(): string
    {
        if ($this->setting->environmentUrl) {
            return $this->setting->environmentUrl;
        }

        return ($this->setting->sslRequired ? 'https://' : 'http://') . $this->site->name;
    }

    public function siteDirectory(): string
    {
        if ($this->site->rootDirectory) {
            return $this->site->rootDirectory;
        }

        if ($this->site->directory && $this->site->webDirectory) {
            return Str::chopEnd(
                subject: $this->site->webDirectory,
                needle: $this->site->directory
            );
        }

        return '';
    }

    public function server(string $serverId): ForgeServerData
    {
        return $this->client->getServer($serverId);
    }

    public function deleteSite(): void
    {
        $this->client->deleteSite($this->setting->server, $this->site->id);
    }

    public function deploySite(bool $waitOnDeploy = false): void
    {
        $this->client->deploySite($this->setting->server, $this->site->id);

        if ($waitOnDeploy) {
            $this->waitUntilDeployCompletes();
        }
    }

    public function enableQuickDeploy(): void
    {
        $this->client->enableQuickDeploy($this->setting->server, $this->site->id);
    }

    public function executeSiteCommand(string $command): void
    {
        $this->client->runSiteCommand($this->setting->server, $this->site->id, $command);
    }

    public function siteEnvironmentFile(): string
    {
        return $this->client->getSiteEnvironment($this->setting->server, $this->site->id);
    }

    public function updateSiteEnvironmentFile(string $content): void
    {
        $this->client->updateSiteEnvironment($this->setting->server, $this->site->id, $content);
    }

    public function siteDeploymentScript(): string
    {
        return $this->client->getSiteDeploymentScript($this->setting->server, $this->site->id);
    }

    public function updateSiteDeploymentScript(string $content, ?bool $autoSource = null): void
    {
        $this->client->updateSiteDeploymentScript($this->setting->server, $this->site->id, $content, $autoSource);
    }

    public function createWebhook(string $url): void
    {
        $this->client->createWebhook($this->setting->server, $this->site->id, $url);
    }

    /**
     * @return array<int, ForgeDaemonData>
     */
    public function daemons(): array
    {
        return $this->client->listDaemons($this->setting->server);
    }

    public function createDaemon(array $payload): void
    {
        if (empty($payload['name']) && ! empty($payload['command'])) {
            $payload['name'] = Str::limit($payload['command'], 120, '');
        }

        $payload['processes'] = (int) ($payload['processes'] ?? 1);

        $this->client->createDaemon($this->setting->server, $payload);
    }

    public function deleteDaemon(string|int $daemonId): void
    {
        $this->client->deleteDaemon($this->setting->server, $daemonId);
    }

    /**
     * @return array<int, ForgeJobData>
     */
    public function jobs(): array
    {
        return $this->client->listJobs($this->setting->server);
    }

    public function createJob(array $payload): void
    {
        $this->client->createJob($this->setting->server, $payload);
    }

    public function deleteJob(string|int $jobId): void
    {
        $this->client->deleteJob($this->setting->server, $jobId);
    }

    /**
     * @return array<int, ForgeDatabaseData>
     */
    public function databases(): array
    {
        return $this->client->listDatabases($this->setting->server);
    }

    public function createDatabase(array $payload): void
    {
        $this->client->createDatabase($this->setting->server, $payload);
    }

    public function deleteDatabase(string|int $databaseId): void
    {
        $this->client->deleteDatabase($this->setting->server, $databaseId);
    }

    /**
     * @return array<int, ForgeDatabaseUserData>
     */
    public function databaseUsers(): array
    {
        return $this->client->listDatabaseUsers($this->setting->server);
    }

    public function createDatabaseUser(array $payload): void
    {
        $this->client->createDatabaseUser($this->setting->server, $payload);
    }

    public function deleteDatabaseUser(string|int $databaseUserId): void
    {
        $this->client->deleteDatabaseUser($this->setting->server, $databaseUserId);
    }

    /**
     * @return array<int, ForgeDomainData>
     */
    public function domains(): array
    {
        return $this->client->listDomains($this->setting->server, $this->site->id);
    }

    public function createDomain(string $domain): ForgeDomainData
    {
        return $this->client->createDomain($this->setting->server, $this->site->id, $domain);
    }

    public function obtainLetsEncryptCertificate(array $domains): void
    {
        $siteDomains = $this->domains();

        foreach ($domains as $domainName) {
            $domain = collect($siteDomains)->firstWhere('name', $domainName);

            if (! $domain) {
                $domain = $this->createDomain($domainName);
            }

            $this->client->enableLetsEncrypt($this->setting->server, $this->site->id, $domain->id);
        }
    }

    protected function waitUntilDeployCompletes(): void
    {
        $startedAt = time();
        $timeoutAt = $startedAt + (int) $this->setting->timeoutSeconds;
        $readyStates = ['deployed', 'installed', 'never-deployed'];
        $failedStates = ['failed', 'removing', 'uninstalling'];

        while (time() <= $timeoutAt) {
            $latestSite = $this->client->getSite($this->setting->server, $this->site->id);
            $this->setSite($latestSite);

            if (in_array($latestSite->status, $readyStates, true)) {
                return;
            }

            if (in_array($latestSite->status, $failedStates, true)) {
                throw new RuntimeException("Site deployment failed with status [{$latestSite->status}].");
            }

            sleep(5);
        }

        throw new RuntimeException('Timed out while waiting for site deployment to complete.');
    }

    protected function waitUntilSiteIsInstalled(ForgeSiteData $site): ForgeSiteData
    {
        $startedAt = time();
        $timeoutAt = $startedAt + (int) $this->setting->timeoutSeconds;
        $readyStates = ['installed', 'deployed', 'never-deployed'];
        $failedStates = ['failed', 'removing', 'uninstalling'];

        $latestSite = $site;

        while (time() <= $timeoutAt) {
            if (in_array($latestSite->status, $readyStates, true)) {
                return $latestSite;
            }

            if (in_array($latestSite->status, $failedStates, true)) {
                throw new RuntimeException("Site provisioning failed with status [{$latestSite->status}].");
            }

            sleep(5);
            $latestSite = $this->client->getSite($this->setting->server, $site->id);
        }

        throw new RuntimeException('Timed out while waiting for site provisioning to complete.');
    }
}
