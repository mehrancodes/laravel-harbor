<?php

use App\Http\Integrations\Forge\Data\SiteData;
use App\Http\Integrations\Forge\Requests\CreateGitRepositoryRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

test('CreateGitRepositoryRequest can install the git repositor', function ($siteData) {
    Saloon::fake([
        CreateGitRepositoryRequest::class => MockResponse::make($siteData),
    ]);

    $res = forgeConnector()->send(
        new CreateGitRepositoryRequest(1, $siteData['site']['name'])
    );

    expect($res->dtoOrFail())
        ->toBeInstanceOf(SiteData::class);
})
    ->with('site_with_repository');

test('CreateGitRepositoryRequest returns proper response fail status', function ($status) {
    Saloon::fake([
        CreateGitRepositoryRequest::class => MockResponse::make([], $status),
    ]);

    $res = forgeConnector()->send(
        new CreateGitRepositoryRequest(1, 'int-12.harbor.com')
    );

    expect($res->status())
        ->toBe($status);
})
    ->with([
        404,
        500,
    ]);
