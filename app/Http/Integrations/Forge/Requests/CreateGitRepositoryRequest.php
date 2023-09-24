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

namespace App\Http\Integrations\Forge\Requests;

use App\Http\Integrations\Forge\Data\SiteData;
use Saloon\Contracts\Response;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class CreateGitRepositoryRequest extends Request
{
    protected Method $method = Method::POST;

    public function __construct(public int $serverId, public string $siteId)
    {
    }

    public function resolveEndpoint(): string
    {
        return 'servers/'.$this->serverId.'/sites/'.$this->siteId.'/git';
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        $data = $response->json()['site'];

        return SiteData::fromResponse($data);
    }

    protected function defaultQuery(): array
    {
        return [
            'provider' => config('services.forge.git.provider'),
            'repository' => config('services.forge.git.repository'),
            'branch' => config('services.forge.git.branch'),
            'composer' => false,
        ];
    }
}
