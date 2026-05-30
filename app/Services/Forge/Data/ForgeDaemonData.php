<?php

declare(strict_types=1);

namespace App\Services\Forge\Data;

use App\Services\Forge\Api\Support\JsonApiData;

class ForgeDaemonData
{
    public function __construct(
        public int|string $id,
        public string $command,
        public ?string $user,
        public ?string $directory,
    ) {
        //
    }

    public static function fromResource(array $resource): self
    {
        $attributes = JsonApiData::attributes($resource);

        return new self(
            id: JsonApiData::id($resource),
            command: (string) ($attributes['command'] ?? ''),
            user: $attributes['user'] ?? null,
            directory: $attributes['directory'] ?? null,
        );
    }
}
