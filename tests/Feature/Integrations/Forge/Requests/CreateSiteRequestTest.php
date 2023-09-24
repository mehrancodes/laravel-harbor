<?php

use App\Http\Integrations\Forge\Data\SiteData;
use App\Http\Integrations\Forge\Requests\CreateSiteRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

test('CreateSiteRequest can return the available site', function ($siteData) {
    Saloon::fake([
        CreateSiteRequest::class => MockResponse::make($siteData),
    ]);

    $res = forgeConnector()->send(
        new CreateSiteRequest(1, $siteData['site']['name'])
    );

    expect($res->dtoOrFail())
        ->toBeInstanceOf(SiteData::class);
})
    ->with('site');

test('CreateSiteRequest returns proper response fail status', function ($status) {
    Saloon::fake([
        CreateSiteRequest::class => MockResponse::make([], $status),
    ]);

    $res = forgeConnector()->send(new CreateSiteRequest(1, 'int-12.hello.com'));

    expect($res->status())
        ->toBe($status);
})
    ->with([
        404,
        500,
    ]);
