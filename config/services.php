<?php

return [
    'forge' => [
        'token' => env('FORGE_TOKEN'),
        'server' => env('FORGE_SERVER'),
        'domain' => env('FORGE_DOMAIN'),
        'repository' => env('FORGE_REPOSITORY'),
        'branch' => env('FORGE_BRANCH'),
        'php_version' => env('FORGE_PHP_VERSION', 'php82'),
        'project_type' => env('FORGE_PROJECT_TYPE', 'php'),
        'subdomain' => [
            'pattern' => env('FORGE_SUBDOMAIN_PATTERN'),
        ],
    ],
];
