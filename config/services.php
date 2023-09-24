<?php

return [
    'forge' => [
        'token' => env('FORGE_TOKEN'),
        'server' => env('FORGE_SERVER'),
        'domain' => env('FORGE_DOMAIN'),
        'git' => [
            'provider' => env('FORGE_GIT_PROVIDER', 'github'),
            'repository' => env('FORGE_GIT_REPOSITORY'),
            'branch' => env('FORGE_GIT_BRANCH'),
        ],
        'php_version' => env('FORGE_PHP_VERSION', 'php82'),
        'project_type' => env('FORGE_PROJECT_TYPE', 'php'),
        'subdomain' => [
            'pattern' => env('FORGE_SUBDOMAIN_PATTERN'),
        ],
        'ssl_required' => env('FORGE_SSL_REQUIRED', true),
        'wait_on_ssl' => env('FORGE_WAIT_ON_SSL', true),
        'quick_deploy' => env('FORGE_QUICK_DEPLOY', true),
        'nginx_template' => env('FORGE_NGINX_TEMPLATE'),
        'site_isolation' => env('FORGE_SITE_ISOLATION', false),
    ],
];
