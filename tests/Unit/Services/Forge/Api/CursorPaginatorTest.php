<?php

use App\Services\Forge\Api\Support\CursorPaginator;

test('it traverses cursor based pages and merges data', function () {
    $paginator = new CursorPaginator();

    $pages = [
        null => [
            'data' => [
                ['id' => 1],
                ['id' => 2],
            ],
            'meta' => [
                'next_cursor' => 'cursor-2',
            ],
        ],
        'cursor-2' => [
            'data' => [
                ['id' => 3],
            ],
            'meta' => [
                'next_cursor' => null,
            ],
        ],
    ];

    $results = $paginator->collect(function (?string $cursor) use ($pages): array {
        return $pages[$cursor];
    });

    expect($results)->toBe([
        ['id' => 1],
        ['id' => 2],
        ['id' => 3],
    ]);
});
