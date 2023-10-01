<?php

return [
    'token' => env('FORGE_TOKEN'),
    'server' => env('FORGE_SERVER'),
    'domain' => env('FORGE_DOMAIN'),
    'git_provider' => env('FORGE_GIT_PROVIDER', 'github'),
    'repository' => env('FORGE_GIT_REPOSITORY'),
    'branch' => env('FORGE_GIT_BRANCH'),

    'subdomain_pattern' => env('FORGE_SUBDOMAIN_PATTERN'),

    'php_version' => env('FORGE_PHP_VERSION', 'php82'),
    'project_type' => env('FORGE_PROJECT_TYPE', 'php'),

    'site_isolation_required' => env('FORGE_SITE_ISOLATION', false),
    'job_scheduler_required' => env('FORGE_JOB_SCHEDULER', false),
    'auto_source_required' => env('FORGE_AUTO_SOURCE_REQUIRED', false),
    'db_creation_required' => env('FORGE_DB_CREATION_REQUIRED', false),
    'ssl_required' => env('FORGE_SSL_REQUIRED', true),

    'wait_on_ssl' => env('FORGE_WAIT_ON_SSL', true),
    'wait_on_deploy' => env('FORGE_WAIT_ON_DEPLOY', true),

    'nginx_template' => env('FORGE_NGINX_TEMPLATE'),
    'nginx_substitute' => env('FORGE_NGINX_SUBSTITUTE'),

    'quick_deploy' => env('FORGE_QUICK_DEPLOY', true),
    'deploy_script' => env('FORGE_DEPLOY_SCRIPT'),
    'env_keys' => env('FORGE_ENV_KEYS'),
];
