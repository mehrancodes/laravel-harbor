<?php

use App\Http\Integrations\Forge\Data\ServerData;
use App\Http\Integrations\Forge\Requests\FindServerRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

test('it returns the server by given id', function ($payload) {
    Saloon::fake([
        FindServerRequest::class => MockResponse::make($payload),
    ]);

    $res = forgeConnector()->send(
        new FindServerRequest($payload['server']['id'])
    );

    expect($res->dto())
        ->toBeInstanceOf(ServerData::class);
})
    ->with('server');

test('it returns proper response fail status', function ($status) {
    Saloon::fake([
        FindServerRequest::class => MockResponse::make([], $status),
    ]);

    $res = forgeConnector()->send(new FindServerRequest(1));

    expect($res->status())
        ->toBe($status);
})
    ->with([
        404,
        500,
    ]);
