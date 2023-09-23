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
use Illuminate\Support\Collection;
use Saloon\Contracts\Response;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListSitesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(public int $serverId)
    {
    }

    public function resolveEndpoint(): string
    {
        return 'servers/'.$this->serverId.'/sites';
    }

    public function createDtoFromResponse(Response $response): Collection
    {
        $data = $response->json()['sites'];

        return collect($data)->map(fn ($data) => SiteData::fromResponse($data));
    }
}
