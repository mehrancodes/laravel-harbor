<?php

return [
    // Forge API authentication token.
    'token' => env('FORGE_TOKEN'),

    // Forge Identifier for the server.
    'server' => env('FORGE_SERVER'),

    // Website's domain name.
    'domain' => env('FORGE_DOMAIN'),

    // Git service provider (default: 'github').
    'git_provider' => env('FORGE_GIT_PROVIDER', 'github'),

    // Git repository name.
    'repository' => env('FORGE_GIT_REPOSITORY'),

    // Git branch name.
    'branch' => env('FORGE_GIT_BRANCH'),

    // Pattern for subdomains.
    'subdomain_pattern' => env('FORGE_SUBDOMAIN_PATTERN'),

    // Deployment script content.
    'deploy_script' => env('FORGE_DEPLOY_SCRIPT'),

    // Template for Nginx configuration.
    'nginx_template' => env('FORGE_NGINX_TEMPLATE'),

    // Key/value pairs for customizing the Nginx template.
    'nginx_substitute' => env('FORGE_NGINX_SUBSTITUTE'),

    // Key/value pairs to be added to the environment file at runtime.
    'env_keys' => env('FORGE_ENV_KEYS'),

    // PHP version (default: 'php82').
    'php_version' => env('FORGE_PHP_VERSION', 'php82'),

    // Type of the project (default: 'php').
    'project_type' => env('FORGE_PROJECT_TYPE', 'php'),

    // Flag indicating if site isolation is needed (default: false).
    'site_isolation_required' => env('FORGE_SITE_ISOLATION', false),

    // Flag indicating if a job scheduler is needed (default: false).
    'job_scheduler_required' => env('FORGE_JOB_SCHEDULER', false),

    // Flag to auto-source environment variables in deployment (default: false).
    'auto_source_required' => env('FORGE_AUTO_SOURCE_REQUIRED', false),

    // Flag indicating if a database should be created (default: false).
    'db_creation_required' => env('FORGE_DB_CREATION_REQUIRED', false),

    // Flag to enable Quick Deploy (default: true).
    'quick_deploy' => env('FORGE_QUICK_DEPLOY', false),

    // Flag to enable SSL certification (default: false).
    'ssl_required' => env('FORGE_SSL_REQUIRED', false),

    // Flag to pause until SSL setup completes during provisioning (default: true).
    'wait_on_ssl' => env('FORGE_WAIT_ON_SSL', true),

    // Flag to pause until site deployment completes during provisioning (default: true).
    'wait_on_deploy' => env('FORGE_WAIT_ON_DEPLOY', true),

];
