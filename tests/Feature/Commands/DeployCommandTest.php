<?php

use Illuminate\Support\Facades\Config;

test('it throw error if forge token env key not defined', function ($keys) {
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
                'services.forge.server' => 'asd',
            ],
            [
                'services.forge.token' => 'asdasd',
                'services.forge.server' => null,
            ],
            [
                'services.forge.token' => 'asdasd',
                'services.forge.server' => 1121,
            ],
        ],
    ]);
