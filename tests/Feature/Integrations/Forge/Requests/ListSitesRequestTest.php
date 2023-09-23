<?php

use App\Http\Integrations\Forge\Requests\ListSitesRequest;
use Illuminate\Support\Collection;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

test('ListSitesRequest can return the available sites by given server', function ($siteData) {
    Saloon::fake([
        ListSitesRequest::class => MockResponse::make($siteData),
    ]);

    $res = forgeConnector()->send(
        new ListSitesRequest(1)
    );

    expect($res->dtoOrFail())
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2);
})
    ->with('sites_list');

test('ListSitesRequest returns proper response fail status', function ($status) {
    Saloon::fake([
        ListSitesRequest::class => MockResponse::make([], $status),
    ]);

    $res = forgeConnector()->send(new ListSitesRequest(1));

    expect($res->status())
        ->toBe($status);
})
    ->with([
        404,
        500,
    ]);
