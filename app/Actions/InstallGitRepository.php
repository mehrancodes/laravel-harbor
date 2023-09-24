<?php

namespace App\Actions;

use App\Http\Integrations\Forge\ForgeConnector;
use App\Http\Integrations\Forge\Requests\CreateGitRepositoryRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use ReflectionException;
use Saloon\Exceptions\InvalidResponseClassException;
use Saloon\Exceptions\PendingRequestException;

class InstallGitRepository
{
    use AsAction;

    /**
     * @throws InvalidResponseClassException
     * @throws ReflectionException
     * @throws PendingRequestException
     */
    public function handle(ForgeConnector $connector, int $serverId, int $siteId): string
    {
        return $connector->send(new CreateGitRepositoryRequest($serverId, $siteId));
    }
}
