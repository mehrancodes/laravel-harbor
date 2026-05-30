<?php

declare(strict_types=1);

namespace App\Services\Forge\Exceptions;

use RuntimeException;

class ValidationException extends RuntimeException
{
    public function __construct(
        protected array $errors,
    ) {
        parent::__construct(implode(PHP_EOL, $errors));
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
