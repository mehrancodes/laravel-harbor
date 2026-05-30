<?php

declare(strict_types=1);

namespace App\Services\Forge\Api\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ForgeRequest extends Request
{
    protected Method $method;

    public function __construct(
        Method $method,
        protected string $endpoint,
        protected array $queryParams = [],
    ) {
        $this->method = $method;
    }

    public function resolveEndpoint(): string
    {
        return $this->endpoint;
    }

    protected function defaultQuery(): array
    {
        return $this->queryParams;
    }
}
