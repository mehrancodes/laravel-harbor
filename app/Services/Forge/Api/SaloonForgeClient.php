<?php

declare(strict_types=1);

namespace App\Services\Forge\Api;

use App\Services\Forge\Api\Requests\ForgeJsonRequest;
use App\Services\Forge\Api\Requests\ForgeRequest;
use App\Services\Forge\Api\Support\CursorPaginator;
use App\Services\Forge\Api\Support\ForgeApiResponse;
use App\Services\Forge\Api\Support\JsonApiData;
use App\Services\Forge\Data\ForgeDaemonData;
use App\Services\Forge\Data\ForgeDatabaseData;
use App\Services\Forge\Data\ForgeDatabaseUserData;
use App\Services\Forge\Data\ForgeDomainData;
use App\Services\Forge\Data\ForgeJobData;
use App\Services\Forge\Data\ForgeServerData;
use App\Services\Forge\Data\ForgeSiteData;
use Saloon\Enums\Method;

class SaloonForgeClient implements ForgeClient
{
    public function __construct(
        protected ForgeConnector $connector,
        protected CursorPaginator $paginator,
        protected string $organization,
    ) {
        //
    }

    public function getServer(string|int $serverId): ForgeServerData
    {
        $payload = $this->sendRequest(Method::GET, $this->endpoint("/servers/{$serverId}"));

        return ForgeServerData::fromResource(JsonApiData::data($payload));
    }

    public function listSites(string|int $serverId): array
    {
        $resources = $this->paginate($this->endpoint("/servers/{$serverId}/sites"));

        return array_map(static fn (array $resource) => ForgeSiteData::fromResource($resource), $resources);
    }

    public function getSite(string|int $serverId, string|int $siteId): ForgeSiteData
    {
        $payload = $this->sendRequest(Method::GET, $this->endpoint("/sites/{$siteId}"));

        return ForgeSiteData::fromResource(JsonApiData::data($payload));
    }

    public function createSite(string|int $serverId, array $payload): ForgeSiteData
    {
        $response = $this->sendRequest(Method::POST, $this->endpoint("/servers/{$serverId}/sites"), $payload);

        return ForgeSiteData::fromResource(JsonApiData::data($response));
    }

    public function deleteSite(string|int $serverId, string|int $siteId): void
    {
        $this->sendRequest(Method::DELETE, $this->endpoint("/servers/{$serverId}/sites/{$siteId}"));
    }

    public function getSiteNginxConfig(string|int $serverId, string|int $siteId): string
    {
        $payload = $this->sendRequest(Method::GET, $this->endpoint("/servers/{$serverId}/sites/{$siteId}/nginx"));

        return (string) (JsonApiData::attributes(JsonApiData::data($payload))['content'] ?? '');
    }

    public function updateSiteNginxConfig(string|int $serverId, string|int $siteId, string $content): void
    {
        $this->sendRequest(Method::PUT, $this->endpoint("/servers/{$serverId}/sites/{$siteId}/nginx"), [
            'config' => $content,
        ]);
    }

    public function getSiteEnvironment(string|int $serverId, string|int $siteId): string
    {
        $payload = $this->sendRequest(Method::GET, $this->endpoint("/servers/{$serverId}/sites/{$siteId}/environment"));

        return (string) (JsonApiData::attributes(JsonApiData::data($payload))['content'] ?? '');
    }

    public function updateSiteEnvironment(string|int $serverId, string|int $siteId, string $content): void
    {
        $this->sendRequest(Method::PUT, $this->endpoint("/servers/{$serverId}/sites/{$siteId}/environment"), [
            'environment' => $content,
        ]);
    }

    public function getSiteDeploymentScript(string|int $serverId, string|int $siteId): string
    {
        $payload = $this->sendRequest(Method::GET, $this->endpoint("/servers/{$serverId}/sites/{$siteId}/deployments/script"));

        return (string) (JsonApiData::attributes(JsonApiData::data($payload))['content'] ?? '');
    }

    public function updateSiteDeploymentScript(string|int $serverId, string|int $siteId, string $content, ?bool $autoSource = null): void
    {
        $payload = [
            'content' => $content,
        ];

        if ($autoSource !== null) {
            $payload['auto_source'] = $autoSource;
        }

        $this->sendRequest(Method::PUT, $this->endpoint("/servers/{$serverId}/sites/{$siteId}/deployments/script"), $payload);
    }

    public function deploySite(string|int $serverId, string|int $siteId): void
    {
        $this->sendRequest(Method::POST, $this->endpoint("/servers/{$serverId}/sites/{$siteId}/deployments"));
    }

    public function enableQuickDeploy(string|int $serverId, string|int $siteId): void
    {
        $this->sendRequest(Method::POST, $this->endpoint("/servers/{$serverId}/sites/{$siteId}/deployments/push-to-deploy"));
    }

    public function disableQuickDeploy(string|int $serverId, string|int $siteId): void
    {
        $this->sendRequest(Method::DELETE, $this->endpoint("/servers/{$serverId}/sites/{$siteId}/deployments/push-to-deploy"));
    }

    public function runSiteCommand(string|int $serverId, string|int $siteId, string $command): void
    {
        $this->sendRequest(Method::POST, $this->endpoint("/servers/{$serverId}/sites/{$siteId}/commands"), [
            'command' => $command,
        ]);
    }

    public function createWebhook(string|int $serverId, string|int $siteId, string $url): void
    {
        $this->sendRequest(Method::POST, $this->endpoint("/servers/{$serverId}/sites/{$siteId}/webhooks"), [
            'url' => $url,
        ]);
    }

    public function listDaemons(string|int $serverId): array
    {
        $resources = $this->paginate($this->endpoint("/servers/{$serverId}/background-processes"));

        return array_map(static fn (array $resource) => ForgeDaemonData::fromResource($resource), $resources);
    }

    public function createDaemon(string|int $serverId, array $payload): void
    {
        $this->sendRequest(Method::POST, $this->endpoint("/servers/{$serverId}/background-processes"), $payload);
    }

    public function deleteDaemon(string|int $serverId, string|int $daemonId): void
    {
        $this->sendRequest(Method::DELETE, $this->endpoint("/servers/{$serverId}/background-processes/{$daemonId}"));
    }

    public function listJobs(string|int $serverId): array
    {
        $resources = $this->paginate($this->endpoint("/servers/{$serverId}/scheduled-jobs"));

        return array_map(static fn (array $resource) => ForgeJobData::fromResource($resource), $resources);
    }

    public function createJob(string|int $serverId, array $payload): void
    {
        $this->sendRequest(Method::POST, $this->endpoint("/servers/{$serverId}/scheduled-jobs"), $payload);
    }

    public function deleteJob(string|int $serverId, string|int $jobId): void
    {
        $this->sendRequest(Method::DELETE, $this->endpoint("/servers/{$serverId}/scheduled-jobs/{$jobId}"));
    }

    public function listDatabases(string|int $serverId): array
    {
        $resources = $this->paginate($this->endpoint("/servers/{$serverId}/database/schemas"));

        return array_map(static fn (array $resource) => ForgeDatabaseData::fromResource($resource), $resources);
    }

    public function createDatabase(string|int $serverId, array $payload): void
    {
        $this->sendRequest(Method::POST, $this->endpoint("/servers/{$serverId}/database/schemas"), $payload);
    }

    public function deleteDatabase(string|int $serverId, string|int $databaseId): void
    {
        $this->sendRequest(Method::DELETE, $this->endpoint("/servers/{$serverId}/database/schemas/{$databaseId}"));
    }

    public function listDatabaseUsers(string|int $serverId): array
    {
        $resources = $this->paginate($this->endpoint("/servers/{$serverId}/database/users"));

        return array_map(static fn (array $resource) => ForgeDatabaseUserData::fromResource($resource), $resources);
    }

    public function createDatabaseUser(string|int $serverId, array $payload): void
    {
        $this->sendRequest(Method::POST, $this->endpoint("/servers/{$serverId}/database/users"), $payload);
    }

    public function deleteDatabaseUser(string|int $serverId, string|int $databaseUserId): void
    {
        $this->sendRequest(Method::DELETE, $this->endpoint("/servers/{$serverId}/database/users/{$databaseUserId}"));
    }

    public function listDomains(string|int $serverId, string|int $siteId): array
    {
        $resources = $this->paginate($this->endpoint("/servers/{$serverId}/sites/{$siteId}/domains"));

        return array_map(static fn (array $resource) => ForgeDomainData::fromResource($resource), $resources);
    }

    public function createDomain(string|int $serverId, string|int $siteId, string $domain): ForgeDomainData
    {
        $payload = $this->sendRequest(Method::POST, $this->endpoint("/servers/{$serverId}/sites/{$siteId}/domains"), [
            'name' => $domain,
            'allow_wildcard_subdomains' => false,
            'www_redirect_type' => 'none',
        ]);

        return ForgeDomainData::fromResource(JsonApiData::data($payload));
    }

    public function enableLetsEncrypt(string|int $serverId, string|int $siteId, string|int $domainRecordId): void
    {
        $this->sendRequest(Method::POST, $this->endpoint("/servers/{$serverId}/sites/{$siteId}/domains/{$domainRecordId}/certificates"), [
            'type' => 'letsencrypt',
            'enable' => true,
            'letsencrypt' => [
                'verification_method' => 'http-01',
                'key_type' => 'ecdsa',
            ],
        ]);
    }

    protected function endpoint(string $path): string
    {
        return "/orgs/{$this->organization}{$path}";
    }

    protected function sendRequest(Method $method, string $endpoint, array $payload = [], array $query = []): array
    {
        $request = $payload === []
            ? new ForgeRequest($method, $endpoint, $query)
            : new ForgeJsonRequest($method, $endpoint, $payload, $query);

        return ForgeApiResponse::payload($this->connector->send($request));
    }

    protected function paginate(string $endpoint): array
    {
        return $this->paginator->collect(function (?string $cursor) use ($endpoint): array {
            $query = [
                'page[size]' => 100,
            ];

            if ($cursor) {
                $query['page[cursor]'] = $cursor;
            }

            return $this->sendRequest(Method::GET, $endpoint, query: $query);
        });
    }
}
