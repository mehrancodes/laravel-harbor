<?php

dataset('site', [
    [
        [
            'site' => [
                'id' => 2,
                'name' => 'site.com',
                'aliases' => [
                    'alias1.com',
                ],
                'username' => 'laravel',
                'directory' => '/test',
                'wildcards' => false,
                'status' => 'installing',
                'repository' => null,
                'repository_provider' => null,
                'repository_branch' => null,
                'repository_status' => null,
                'quick_deploy' => false,
                'project_type' => 'php',
                'app' => null,
                'php_version' => 'php81',
                'app_status' => null,
                'slack_channel' => null,
                'telegram_chat_id' => null,
                'telegram_chat_title' => null,
                'deployment_url' => '...',
                'created_at' => '2016-12-16 16:38:08',
                'tags' => [],
            ],
        ],
    ],
]);

dataset('sites_list', [
    [
        [
            'sites' => [
                [
                    'id' => 1,
                    'name' => 'mehran.com',
                    'aliases' => [
                        'alias1.com',
                    ],
                    'username' => 'laravel',
                    'directory' => '/test',
                    'wildcards' => false,
                    'status' => 'installing',
                    'repository' => null,
                    'repository_provider' => null,
                    'repository_branch' => null,
                    'repository_status' => null,
                    'quick_deploy' => false,
                    'project_type' => 'php',
                    'app' => null,
                    'php_version' => 'php81',
                    'app_status' => null,
                    'slack_channel' => null,
                    'telegram_chat_id' => null,
                    'telegram_chat_title' => null,
                    'deployment_url' => '...',
                    'created_at' => '2016-12-16 16:38:08',
                    'tags' => [],
                ],
                [
                    'id' => 2,
                    'name' => 'rasulian.com',
                    'aliases' => [
                        'alias2.com',
                    ],
                    'username' => 'laravel',
                    'directory' => '/test',
                    'wildcards' => false,
                    'status' => 'installing',
                    'repository' => null,
                    'repository_provider' => null,
                    'repository_branch' => null,
                    'repository_status' => null,
                    'quick_deploy' => false,
                    'project_type' => 'php',
                    'app' => null,
                    'php_version' => 'php81',
                    'app_status' => null,
                    'slack_channel' => null,
                    'telegram_chat_id' => null,
                    'telegram_chat_title' => null,
                    'deployment_url' => '...',
                    'created_at' => '2016-12-16 16:38:08',
                    'tags' => [],
                ],
            ],
        ],
    ],
]);

dataset('site_with_repository', [
    [
        [
            'site' => [
                'id' => 2110868,
                'server_id' => 719481,
                'name' => 'int-12.harbor.com',
                'aliases' => [

                ],
                'directory' => "\/public",
                'wildcards' => false,
                'status' => 'installing',
                'repository' => "mehrancodes\/harbor",
                'repository_provider' => 'github',
                'repository_branch' => 'int-12',
                'repository_status' => 'installing',
                'quick_deploy' => false,
                'deployment_status' => null,
                'project_type' => 'php',
                'php_version' => 'php82',
                'app' => null,
                'app_status' => null,
                'slack_channel' => null,
                'telegram_chat_id' => null,
                'telegram_chat_title' => null,
                'teams_webhook_url' => null,
                'discord_webhook_url' => null,
                'created_at' => '2023-09-24 14:52:03',
                'telegram_secret' => "\/start@laravel_forge_telegram_bot eyJpdiI6ImloNE5KczliV1YrVUhHZE8xbVZUa2c9PSIsInZhbHVlIjoiTEFEckVqUGp5Si95Q1ZnQWlITFh3Sm9JNWFmTmx6czg0dmd1eXI5TW9rbmlDdHFTeEl6c3czdmdheFliYlV5L0lQQ242YWF2ajIxL3JSdTA3UHFpNHc9PSIsIm1hYyI6IjAzOWJjZWQ0ZWFmZDFlOTYyOWZkMmYyMTVhMDg0ZGIyYjMxOGNmZDY1ZmI4ZTY1ODIxOGRlNjkzZWNmMDc2NmEiLCJ0YWciOiIifQ==",
                'username' => 'forge',
                'deployment_url' => "https:\/\/forge.laravel.com\/servers\/719481\/sites\/2110868\/deploy\/http?token=ZFNnWbTpZ7XBadXTN4NYwB0gpTB5SYeN3ujMAD26",
                'is_secured' => false,
            ],
        ],
    ],
]);
