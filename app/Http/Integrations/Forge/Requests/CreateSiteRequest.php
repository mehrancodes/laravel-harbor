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

class CreateSiteRequest extends Request
{
    protected Method $method = Method::POST;

    public function __construct(public int $serverId, public string $domain)
    {
    }

    public function resolveEndpoint(): string
    {
        return 'servers/'.$this->serverId.'/sites';
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        $data = $response->json()['site'];

        return SiteData::fromResponse($data);
    }

    protected function defaultQuery(): array
    {
        return [
            'domain' => $this->domain,
            'project_type' => config('services.forge.project_type'),
            'php_version' => config('services.forge.php_version'),
            'directory' => '/public',
        ];
    }
}
