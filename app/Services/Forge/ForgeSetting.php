<?php

declare(strict_types=1);

/**
 * This file is part of Harbor CLI.
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
     * The API token.
     */
    public string $token;

    /**
     * The server ID.
     */
    public string $server;

    /**
     * The site domain.
     */
    public string $domain;

    /**
     * The git provider.
     */
    public string $gitProvider;

    /**
     * The git repository.
     */
    public string $repository;

    /**
     * The git branch..
     */
    public string $branch;

    /**
     * The subdomain pattern.
     */
    public ?string $subdomainPattern = null;

    /**
     * The project type
     */
    public string $projectType;

    /**
     * The PHP version
     */
    public string $phpVersion;

    /**
     * The Nginx template
     */
    public string $nginxTemplate;

    /**
     * Weather enable Quick Deploy.
     */
    public bool $quickDeploy;

    /**
     * Weather required to run site isolation.
     */
    public bool $siteIsolationRequired;

    /**
     * Weather required to run jobs scheduler.
     */
    public bool $jobSchedulerRequired;

    /**
     * Weather required to create database.
     */
    public bool $dbCreationRequired;

    /**
     * Whether to automatically source environment variables into the deployment script.
     */
    public bool $autoSourceRequired;

    /**
     * Whether to create the SSL certification.
     */
    public bool $sslRequired;

    /**
     * Wait on the site deployment during the provision.
     */
    public bool $waitOnDeploy;

    /**
     * Wait on the site deployment during the provision.
     */
    public bool $waitOnSsl;

    /**
     * A comma-separated string of key/values to update custom keys in the Nginx site template.
     */
    public ?string $nginxSubstitute = null;

    /**
     * The contents of the deployment script.
     */
    public ?string $deployScript = null;

    /**
     * The contents of the custom key/values to get added in the environment file on runtime.
     */
    public ?string $envKeys = null;

    /**
     * The custom command to run.
     */
    public ?string $command = null;

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
