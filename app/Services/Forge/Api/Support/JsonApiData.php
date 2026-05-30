<?php

declare(strict_types=1);

namespace App\Services\Forge\Api\Support;

class JsonApiData
{
    public static function data(array $payload): array
    {
        return $payload['data'] ?? [];
    }

    public static function attributes(array $resource): array
    {
        return $resource['attributes'] ?? [];
    }

    public static function id(array $resource): string|int
    {
        $id = $resource['id'] ?? null;

        if (! is_string($id) && ! is_int($id)) {
            throw new \UnexpectedValueException('Invalid Forge API resource: missing id.');
        }

        return $id;
    }

    public static function relationshipIds(array $resource, string $relationship): array
    {
        $items = $resource['relationships'][$relationship]['data'] ?? [];

        if (! is_array($items)) {
            return [];
        }

        if (isset($items['id'])) {
            return [$items['id']];
        }

        return collect($items)
            ->pluck('id')
            ->filter()
            ->values()
            ->all();
    }

    public static function nextCursor(array $payload): ?string
    {
        return $payload['meta']['next_cursor'] ?? null;
    }
}
