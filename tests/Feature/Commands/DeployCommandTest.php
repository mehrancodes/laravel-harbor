<?php

use Illuminate\Support\Facades\Config;

test('it throw error if forge required config not prepared', function ($keys) {
    foreach ($keys as $key => $value) {
        Config::set($key, $value);
    }

    $this->artisan('deploy')
        ->assertFailed();
})
    ->with([
        [
            [
                'services.forge.token' => null,
                'services.forge.server' => 'fake_id',
            ],
            [
                'services.forge.token' => 'fake_token',
                'services.forge.server' => null,
            ],
            [
                'services.forge.token' => 'fake_token',
                'services.forge.server' => 1121,
            ],
        ],
    ]);
