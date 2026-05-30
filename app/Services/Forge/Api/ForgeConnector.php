<?php

declare(strict_types=1);

namespace App\Services\Forge\Api;

use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;

class ForgeConnector extends Connector
{
    public function __construct(
        protected string $token,
        protected int $timeoutSeconds = 180,
    ) {
        //
    }

    public function resolveBaseUrl(): string
    {
        return 'https://forge.laravel.com/api';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout' => $this->timeoutSeconds,
        ];
    }

    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator($this->token);
    }
}
