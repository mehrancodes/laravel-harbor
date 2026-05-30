<?php

declare(strict_types=1);

namespace App\Services\Forge\Api\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class ForgeJsonRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method;

    public function __construct(
        Method $method,
        protected string $endpoint,
        protected array $bodyPayload = [],
        protected array $queryParams = [],
    ) {
        $this->method = $method;
    }

    public function resolveEndpoint(): string
    {
        return $this->endpoint;
    }

    protected function defaultBody(): array
    {
        return $this->bodyPayload;
    }

    protected function defaultQuery(): array
    {
        return $this->queryParams;
    }
}
