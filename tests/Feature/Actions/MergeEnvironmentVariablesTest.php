<?php

use App\Actions\MergeEnvironmentVariables;

it('it can merge custom variables with source environment variables securely', function ($actual, $expected) {
    expect(
        MergeEnvironmentVariables::run(
            $actual['source'],
            $actual['content']
        )
    )
        ->toBe($expected);
})
    ->with([
        [
            'actual' => [
                'source' => "APP_NAME=Laravel\n\nAPP_KEY=\nAPP_ENV=local\n",
                'content' => [
                    'GOOGLE_API' => 'MY_API_KEY',
                    'APP_KEY' => 'APP_KEY_VALUE',
                ],
            ],
            'expected' => "APP_NAME=Laravel\n\nAPP_KEY=APP_KEY_VALUE\nAPP_ENV=local\n\nGOOGLE_API=MY_API_KEY\n",
        ],
        [
            'actual' => [
                'source' => "APP_NAME=Laravel\n\nPUSHER_APP_ID=\n\nAPP_ENV=local\n",
                'content' => [
                    'GOOGLE_API' => 'MY_API_KEY',
                ],
            ],
            'expected' => "APP_NAME=Laravel\n\nPUSHER_APP_ID=\n\nAPP_ENV=local\n\nGOOGLE_API=MY_API_KEY\n",
        ],
        [
            'actual' => [
                'source' => "APP_NAME=Laravel\n\n\n",
                'content' => [
                    'GOOGLE_API' => 'MY_API_KEY',
                ],
            ],
            'expected' => "APP_NAME=Laravel\n\n\n\nGOOGLE_API=MY_API_KEY\n",
        ],
        [
            'actual' => [
                'source' => "APP_NAME=Laravel\n\n",
                'content' => [
                    'APP_NAME' => 'Project Name',
                ],
            ],
            'expected' => "APP_NAME=Project Name\n\n\n",
        ],
        [
            'actual' => [
                'source' => "=Laravel\n\n",
                'content' => [
                    'APP_KEY' => 'APP_KEY_VALUE',
                ],
            ],
            'expected' => "\n\nAPP_KEY=APP_KEY_VALUE\n",
        ],
        [
            'actual' => [
                'source' => '',
                'content' => [
                    'APP_NAME' => 'Project Name',
                ],
            ],
            'expected' => "APP_NAME=Project Name\n",
        ],
        [
            'actual' => [
                'source' => "APP_NAME=Laravel\n# Here be dragons\nAPP_ENV=local\n",
                'content' => [],
            ],
            'expected' => "APP_NAME=Laravel\n# Here be dragons\nAPP_ENV=local\n\n",
        ],
    ]);
