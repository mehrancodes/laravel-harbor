<?php

use App\Http\Integrations\Forge\Data\ServerData;

test('ServerData can transfer data', function ($data) {
    $dto = ServerData::fromResponse($data['server']);

    expect($dto)
        ->toBeInstanceOf(ServerData::class)
        ->and($dto->id)->toBe($data['server']['id'])
        ->and($dto->name)->toBe($data['server']['name']);
})
    ->with('server');
