<?php

namespace App\Actions;

use App\Http\Integrations\Forge\Data\SiteData;
use App\Http\Integrations\Forge\ForgeConnector;
use App\Http\Integrations\Forge\Requests\ListSitesRequest;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use ReflectionException;
use Saloon\Exceptions\InvalidResponseClassException;
use Saloon\Exceptions\PendingRequestException;

class FindSite
{
    use AsAction;

    /**
     * @throws InvalidResponseClassException
     * @throws ReflectionException
     * @throws PendingRequestException
     */
    public function handle(ForgeConnector $connector, int $serverId, string $domain): ?SiteData
    {
        /** @var Collection $sites */
        $sites = $connector->send(
            new ListSitesRequest($serverId)
        )->dtoOrFail();

        return $sites->filter(fn ($site) => $site->name === $domain)
            ->first();
    }
}
