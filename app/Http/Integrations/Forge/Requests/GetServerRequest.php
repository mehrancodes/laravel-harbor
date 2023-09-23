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

use App\Http\Integrations\Forge\Data\ServerData;
use Saloon\Contracts\Response;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetServerRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(public string $id)
    {
    }

    public function resolveEndpoint(): string
    {
        return 'servers/'.$this->id;
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        $data = $response->json()['server'];

        return ServerData::fromResponse($data);
    }
}
