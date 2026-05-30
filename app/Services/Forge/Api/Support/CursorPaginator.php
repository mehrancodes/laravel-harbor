<?php

declare(strict_types=1);

namespace App\Services\Forge\Api\Support;

class CursorPaginator
{
    public function collect(callable $fetchPage): array
    {
        $cursor = null;
        $results = [];

        do {
            $payload = $fetchPage($cursor);
            $results = array_merge($results, JsonApiData::data($payload));
            $cursor = JsonApiData::nextCursor($payload);
        } while ($cursor);

        return $results;
    }
}
