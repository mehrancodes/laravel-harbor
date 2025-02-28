<?php

declare(strict_types=1);

/**
 * This file is part of Laravel Harbor.
 *
 * (c) Mehran Rasulian <mehran.rasulian@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace App\Services\Forge;

use App\Rules\BranchNameRegex;
use App\Traits\Outputifier;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Laravel\Forge\Exceptions\ValidationException;

class ForgeSetting
{
    use Outputifier;

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
     * The git repository URL to be used with "custom" service provider
     */
    public ?string $repositoryUrl;

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
    public ?string $phpVersion = null;

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
    public ?string $nginxVariables = null;

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
     * Username for the site isolation (default: formated branch name).
     */
    public ?string $siteIsolationUsername;

    /**
     * Flag indicating if a job scheduler is needed.
     */
    public bool $jobSchedulerRequired;

    /**
     * Flag indicating if a database should be created.
     */
    public bool $dbCreationRequired;

    /**
     * The name of the database to be created
     */
    public ?string $dbName;

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
     * Git token.
     */
    public ?string $gitToken;

    /**
     * Git Issue number.
     */
    public ?string $gitIssueNumber;

    /**
     * Enable git comment on pull requests.
     */
    public bool $gitCommentEnabled;

    /**
     * Gets used to set the site subdomain manually.
     */
    public ?string $subdomainName;

    /**
     * Gets used to set the site domain manually.
     */
    public ?string $environmentUrl;

    /**
     * Is the Slack integration enabled?
     */
    public bool $slackAnnouncementEnabled;

    /**
     * The OAuth Token for the Slack Bot
     */
    public ?string $slackBotUserOauthToken;

    /**
     * Which channel to announce to on Slack
     */
    public ?string $slackChannel;

    /**
     * Enable support for Inertia SSR
     */
    public bool $inertiaSsrEnabled;

    /**
     * Enable github deploy key creation
     */
    public bool $githubCreateDeployKey;

    /**
     * The webhook URL to be added to the Forge site
     */
    public ?string $webhookUrl;

    /**
     * The queue workers to create on new site installation
     */
    public ?string $queueWorkers;

    /**
     * The daemons to create on new site installation
     */
    public ?string $daemons;

    public function __construct()
    {
        $this->init(config('forge'));
    }

    private function init(array $configurations): void
    {
        $validator = $this->validate($configurations);

        throw_if($validator->fails(), ValidationException::class, $validator->errors()->all());

        // If validation passes, set properties
        foreach ($configurations as $key => $value) {
            if (property_exists($this, $key = Str::camel($key))) {
                $this->$key = $value;
            }
        }
    }

    protected function validate(array $configurations): \Illuminate\Validation\Validator
    {
        return Validator::make($configurations, [
            'token' => ['required'],
            'server' => ['required'],
            'domain' => ['required'],
            'git_provider' => ['required'],
            'repository' => ['required'],
            'repository_url' => ['nullable', 'string', 'required_if:git_provider,custom'],
            'branch' => ['required', new BranchNameRegex],
            'project_type' => ['string'],
            'php_version' => ['nullable', 'string'],
            'subdomain_pattern' => ['nullable', 'string'],
            'command' => ['nullable', 'string'],
            'nginx_template' => ['nullable', 'int'],
            'quick_deploy' => ['boolean'],
            'site_isolation_required' => ['boolean'],
            'site_isolation_username' => ['nullable', 'string'],
            'job_scheduler_required' => ['boolean'],
            'db_creation_required' => ['boolean'],
            'db_name' => ['nullable', 'string'],
            'auto_source_required' => ['boolean'],
            'ssl_required' => ['boolean'],
            'wait_on_ssl' => ['boolean'],
            'wait_on_deploy' => ['boolean'],
            'timeout_seconds' => ['required', 'int', 'min:0'],
            'git_comment_enabled' => ['required', 'boolean'],
            'git_issue_number' => ['exclude_if:git_comment_enabled,false', 'required', 'string'],
            'git_token' => ['exclude_if:git_comment_enabled,false', 'required', 'string'],
            'subdomain_name' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9-_]+$/'],
            'environment_url' => ['nullable', 'url'],
            'webhook_url' => ['nullable', 'url'],
            'slack_announcement_enabled' => ['required', 'boolean'],
            'slack_bot_user_oauth_token' => ['exclude_if:slack_announcement_enabled,false', 'required', 'string'],
            'slack_channel' => ['exclude_if:slack_announcement_enabled,false', 'required', 'string'],
            'inertia_ssr_enabled' => ['required', 'boolean'],
            'github_create_deploy_key' => ['required', 'boolean'],
            'queue_workers' => ['nullable', 'string'],
            'daemons' => ['nullable', 'string'],
        ])->sometimes('git_provider', 'in:custom', function (Fluent $input) {
            return $input->github_create_deploy_key === true;
        });
    }
}
