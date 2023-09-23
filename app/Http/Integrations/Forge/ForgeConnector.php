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

namespace App\Http\Integrations\Forge;

use App\Http\Integrations\Forge\Resources\ServerResource;
use App\Http\Integrations\Forge\Resources\SiteResource;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class ForgeConnector extends Connector
{
    use AcceptsJson;

    const API_URL = 'https://forge.laravel.com/api/v1';

    public function __construct(string $token)
    {
        $this->withTokenAuth($token);
    }

    public function resolveBaseUrl(): string
    {
        return self::API_URL;
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultConfig(): array
    {
        return [];
    }

    public function server(): ServerResource
    {
        return new ServerResource($this);
    }

    public function site(): SiteResource
    {
        return new SiteResource($this);
    }
}
