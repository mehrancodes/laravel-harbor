<?php

use App\Http\Integrations\Forge\Resources\ServerResource;

test('ForgeConnector returns proper resource by calling server method', function () {
    expect(forgeConnector()->server())
        ->toBeInstanceOf(ServerResource::class);
});
