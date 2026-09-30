<?php

declare(strict_types=1);

namespace App\Services\Forge\Data;

use App\Services\Forge\Api\Support\JsonApiData;

class ForgeSiteCommandData
{
    public function __construct(
        public int|string $id,
        public int|string $serverId,
        public int|string $siteId,
        public string $command,
        public ?string $status,
        public ?string $output,
        public ?int $exitCode,
    ) {
        //
    }

    public static function fromResource(array $resource, int|string $serverId, int|string $siteId): self
    {
        $attributes = JsonApiData::attributes($resource);

        return new self(
            id: JsonApiData::id($resource),
            serverId: $serverId,
            siteId: $siteId,
            command: (string) ($attributes['command'] ?? ''),
            status: $attributes['status'] ?? null,
            output: $attributes['output'] ?? $attributes['error_output'] ?? null,
            exitCode: isset($attributes['exit_code']) ? (int) $attributes['exit_code'] : null,
        );
    }
}
