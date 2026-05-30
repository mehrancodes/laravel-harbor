<?php

declare(strict_types=1);

namespace App\Services\Forge\Api\Exceptions;

use RuntimeException;

class ForgeApiException extends RuntimeException
{
    public function __construct(
        string $message,
        protected int $statusCode = 0,
        protected array $errors = [],
    ) {
        parent::__construct($message, $statusCode);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
