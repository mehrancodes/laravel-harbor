<?php

declare(strict_types=1);

namespace App\Services\Forge\Api;

use App\Services\Forge\Data\ForgeDaemonData;
use App\Services\Forge\Data\ForgeDatabaseData;
use App\Services\Forge\Data\ForgeDatabaseUserData;
use App\Services\Forge\Data\ForgeDomainData;
use App\Services\Forge\Data\ForgeJobData;
use App\Services\Forge\Data\ForgeServerData;
use App\Services\Forge\Data\ForgeSiteData;

interface ForgeClient
{
    public function getServer(string|int $serverId): ForgeServerData;

    /**
     * @return array<int, ForgeSiteData>
     */
    public function listSites(string|int $serverId): array;

    public function getSite(string|int $serverId, string|int $siteId): ForgeSiteData;

    public function createSite(string|int $serverId, array $payload): ForgeSiteData;

    public function deleteSite(string|int $serverId, string|int $siteId): void;

    public function getSiteNginxConfig(string|int $serverId, string|int $siteId): string;

    public function updateSiteNginxConfig(string|int $serverId, string|int $siteId, string $content): void;

    public function getSiteEnvironment(string|int $serverId, string|int $siteId): string;

    public function updateSiteEnvironment(string|int $serverId, string|int $siteId, string $content): void;

    public function getSiteDeploymentScript(string|int $serverId, string|int $siteId): string;

    public function updateSiteDeploymentScript(string|int $serverId, string|int $siteId, string $content, ?bool $autoSource = null): void;

    public function deploySite(string|int $serverId, string|int $siteId): void;

    public function enableQuickDeploy(string|int $serverId, string|int $siteId): void;

    public function disableQuickDeploy(string|int $serverId, string|int $siteId): void;

    public function runSiteCommand(string|int $serverId, string|int $siteId, string $command): void;

    public function createWebhook(string|int $serverId, string|int $siteId, string $url): void;

    /**
     * @return array<int, ForgeDaemonData>
     */
    public function listDaemons(string|int $serverId): array;

    public function createDaemon(string|int $serverId, array $payload): void;

    public function deleteDaemon(string|int $serverId, string|int $daemonId): void;

    /**
     * @return array<int, ForgeJobData>
     */
    public function listJobs(string|int $serverId): array;

    public function createJob(string|int $serverId, array $payload): void;

    public function deleteJob(string|int $serverId, string|int $jobId): void;

    /**
     * @return array<int, ForgeDatabaseData>
     */
    public function listDatabases(string|int $serverId): array;

    public function createDatabase(string|int $serverId, array $payload): void;

    public function deleteDatabase(string|int $serverId, string|int $databaseId): void;

    /**
     * @return array<int, ForgeDatabaseUserData>
     */
    public function listDatabaseUsers(string|int $serverId): array;

    public function createDatabaseUser(string|int $serverId, array $payload): void;

    public function deleteDatabaseUser(string|int $serverId, string|int $databaseUserId): void;

    /**
     * @return array<int, ForgeDomainData>
     */
    public function listDomains(string|int $serverId, string|int $siteId): array;

    public function createDomain(string|int $serverId, string|int $siteId, string $domain): ForgeDomainData;

    public function enableLetsEncrypt(string|int $serverId, string|int $siteId, string|int $domainRecordId): void;
}
