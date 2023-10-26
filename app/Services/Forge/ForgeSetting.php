<?php

declare(strict_types=1);

/**
 * This file is part of Veyoze CLI.
 *
 * (c) Mehran Rasulian <mehran.rasulian@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace App\Services\Forge;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ForgeSetting
{
    /**
     * Forge API authentication token.
     */
    public string $token;

    /**
     * Forge Identifier for the server.
     */
    public string $server;

    /**
     * Website's domain name.
     */
    public string $domain;

    /**
     * Git service provider (e.g., github, gitlab).
     */
    public string $gitProvider;

    /**
     * Git repository URL or name.
     */
    public string $repository;

    /**
     * Git branch name.
     */
    public string $branch;

    /**
     * Type of the project (e.g., Laravel, WordPress).
     */
    public string $projectType;

    /**
     * PHP version (e.g., 7.4, 8.0).
     */
    public string $phpVersion;

    /**
     * Pattern for subdomains.
     */
    public ?string $subdomainPattern = null;

    /**
     * Template for Nginx configuration.
     */
    public ?string $nginxTemplate = null;

    /**
     * Key/value pairs for customizing the Nginx template.
     */
    public ?string $nginxSubstitute = null;

    /**
     * Deployment script content.
     */
    public ?string $deployScript = null;

    /**
     * Key/value pairs to be added to the environment file at runtime.
     */
    public ?string $envKeys = null;

    /**
     * Custom command to execute.
     */
    public ?string $command = null;

    /**
     * Set the Forge timeout.
     */
    public string|int $timeoutSeconds;

    /**
     * Flag to enable Quick Deploy.
     */
    public bool $quickDeploy;

    /**
     * Flag indicating if site isolation is needed.
     */
    public bool $siteIsolationRequired;

    /**
     * Flag indicating if a job scheduler is needed.
     */
    public bool $jobSchedulerRequired;

    /**
     * Flag indicating if a database should be created.
     */
    public bool $dbCreationRequired;

    /**
     * Flag to auto-source environment variables in deployment.
     */
    public bool $autoSourceRequired;

    /**
     * Flag to enable SSL certification.
     */
    public bool $sslRequired;

    /**
     * Flag to pause until site deployment completes during provisioning.
     */
    public bool $waitOnDeploy;

    /**
     * Flag to pause until SSL setup completes during provisioning.
     */
    public bool $waitOnSsl;

    /**
     * The validation rules.
     */
    private array $validationRules = [
        'token' => ['required'],
        'server' => ['required'],
        'domain' => ['required'],
        'git_provider' => ['required'],
        'repository' => ['required'],
        'branch' => ['required'],
        'project_type' => ['string'],
        'php_version' => ['string'],
        'subdomain_pattern' => ['nullable', 'string'],
        'command' => ['nullable', 'string'],
        'nginx_template' => ['nullable', 'int'],
        'quick_deploy' => ['boolean'],
        'site_isolation_required' => ['boolean'],
        'job_scheduler_required' => ['boolean'],
        'db_creation_required' => ['boolean'],
        'auto_source_required' => ['boolean'],
        'ssl_required' => ['boolean'],
        'wait_on_ssl' => ['boolean'],
        'wait_on_deploy' => ['boolean'],
        'timeout_seconds' => ['required', 'int', 'min:0'],
    ];

    public function __construct()
    {
        $this->init(config('forge'));
    }

    private function init(array $configurations): void
    {
        $validator = Validator::make($configurations, $this->validationRules);

        if ($validator->fails()) {
            // Handle validation failures.
            throw new InvalidArgumentException(
                'Invalid configuration values: '.implode(', ', $validator->errors()->all())
            );
        }

        // If validation passes, set properties
        foreach ($configurations as $key => $value) {
            if (property_exists($this, $key = Str::camel($key))) {
                $this->$key = $value;
            }
        }
    }
}
