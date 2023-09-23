<?php

use App\Http\Integrations\Forge\Data\SiteData;

test('SiteData can transfer data', function ($data) {
    $dto = SiteData::fromResponse($data['site']);

    expect($dto)
        ->toBeInstanceOf(SiteData::class)
        ->and($dto->id)->toBe($data['site']['id'])
        ->and($dto->name)->toBe($data['site']['name']);
})
    ->with('site');
