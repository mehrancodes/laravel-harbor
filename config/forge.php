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
    'nginx_variables' => env('FORGE_NGINX_VARIABLES'),

    // Key/value pairs to be added to the environment file at runtime.
    'env_keys' => env('FORGE_ENV_KEYS'),

    // PHP version (default: 'php82').
    'php_version' => env('FORGE_PHP_VERSION'),

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

    // Override default database and database username, if needed. Defaults to the site name.
    'db_name' => env('FORGE_DB_NAME', null),

    // Import the database via a SQL file (default: null).
    'db_import_sql' => env('FORGE_DB_IMPORT_SQL', null),

    // Seed the database (default: false).
    'db_seed' => env('FORGE_DB_SEED', false),

    // Flag to perform database import on deployment (default: false).
    'db_import_on_deployment' => env('FORGE_DB_IMPORT_ON_DEPLOYMENT', false),

    // Flag to enable SSL certification (default: false).
    'ssl_required' => env('FORGE_SSL_REQUIRED', false),

    // Flag to pause until SSL setup completes during provisioning (default: true).
    'wait_on_ssl' => env('FORGE_WAIT_ON_SSL', true),

    // Flag to pause until site deployment completes during provisioning (default: true).
    'wait_on_deploy' => env('FORGE_WAIT_ON_DEPLOY', true),

    // Set the Forge timeout. (default: 180).
    'timeout_seconds' => env('FORGE_TIMEOUT_SECONDS', 180),

    // Set the git token.
    'git_token' => env('GIT_TOKEN'),

    // Enable provision site information comment on pull requests.
    'git_comment_enabled' => env('GIT_COMMENT_ENABLED', false),

    // Set the git issue number used for adding comments on pull requests.
    'git_issue_number' => env('GIT_ISSUE_NUMBER'),

    // Subdomain name used for the Forge site domain instead of branch name.
    'subdomain_name' => env('SUBDOMAIN_NAME'),

    // Environment URL used for the provision site information comment.
    'environment_url' => env('FORGE_ENVIRONMENT_URL'),

    // The token that will be used to send notifications a Slack channel.
    'slack_announcement_enabled' => env('SLACK_ANNOUNCEMENT_ENABLED', false),

    // The token that will be used to send notifications a Slack channel.
    'slack_bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),

    // The channel that will be used to send notifications about site provisioning
    'slack_channel' => env('SLACK_CHANNEL'),

    // Used to create a Forge daemon to start inertia ssr.
    'inertia_ssr_enabled' => env('INERTIA_SSR_ENABLED', false),
];
