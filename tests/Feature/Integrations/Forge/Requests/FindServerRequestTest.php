<?php

use App\Http\Integrations\Forge\Data\ServerData;
use App\Http\Integrations\Forge\Requests\GetServerRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

test('GetServerRequest returns the server by given id', function ($serverData) {
    Saloon::fake([
        GetServerRequest::class => MockResponse::make($serverData),
    ]);

    $res = forgeConnector()->send(
        new GetServerRequest($serverData['server']['id'])
    );

    expect($res->dto())
        ->toBeInstanceOf(ServerData::class);
})
    ->with('server');

test('GetServerRequest returns proper response fail status', function ($status) {
    Saloon::fake([
        GetServerRequest::class => MockResponse::make([], $status),
    ]);

    $res = forgeConnector()->send(new GetServerRequest(1));

    expect($res->status())
        ->toBe($status);
})
    ->with([
        404,
        500,
    ]);
