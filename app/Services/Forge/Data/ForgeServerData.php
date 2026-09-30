<?php

declare(strict_types=1);

namespace App\Services\Forge\Data;

use App\Services\Forge\Api\Support\JsonApiData;

class ForgeServerData
{
    public function __construct(
        public int|string $id,
        public ?string $name,
        public ?string $ipAddress,
        public ?string $databaseType = null,
    ) {
        //
    }

    public static function fromResource(array $resource): self
    {
        $attributes = JsonApiData::attributes($resource);

        return new self(
            id: JsonApiData::id($resource),
            name: $attributes['name'] ?? null,
            ipAddress: $attributes['ip_address'] ?? null,
            databaseType: $attributes['database_type'] ?? null,
        );
    }
}
