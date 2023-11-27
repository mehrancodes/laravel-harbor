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

namespace App\Services\Github;

use App\Services\Forge\ForgeSetting;
use Illuminate\Support\Facades\Http;
use Laravel\Forge\Exceptions\ValidationException;

class GithubService
{
    private const API_ACCEPT = 'application/vnd.github+json';

    private const API_VERSION = '2022-11-28';

    private const ENVIRONMENT = 'veyoze-provision';

    private const TASK_TYPE = 'deploy';

    private const AUTO_MERGE = false;

    private const PRODUCTION_ENVIRONMENT = false;

    private const SUCCESS_STATE = 'success';

    public function __construct(public ForgeSetting $setting)
    {
    }

    public function createDeployment(string $gitToken, string $repository, string $branch)
    {
        $uri = sprintf('https://api.github.com/repos/%s/deployments', $repository);

        $result = Http::withHeaders([
            'accepts' => self::API_ACCEPT,
            'X-GitHub-Api-Version' => self::API_VERSION,
            'Authorization' => sprintf('Bearer %s', $gitToken),
        ])
            ->post($uri, [
                'ref' => $branch,
                'environment' => self::ENVIRONMENT,
                'production_environment' => self::PRODUCTION_ENVIRONMENT,
                'task' => self::TASK_TYPE,
                'auto_merge' => self::AUTO_MERGE,
            ]);

        throw_if($result->failed(), ValidationException::class, [$result->body()]);

        return json_decode($result->body(), true);
    }

    public function markAsDeployed(string $gitToken, string $repository, int $deploymentId, string $environmentUrl)
    {
        $uri = sprintf(
            'https://api.github.com/repos/%s/deployments/%s/statuses',
            $repository,
            $deploymentId
        );

        $result = Http::withHeaders([
            'accepts' => self::API_ACCEPT,
            'X-GitHub-Api-Version' => self::API_VERSION,
            'Authorization' => sprintf('Bearer %s', $gitToken),
        ])
            ->post($uri, [
                'state' => self::SUCCESS_STATE,
                'environment' => self::ENVIRONMENT,
                'environment_url' => $environmentUrl,
            ]);

        throw_if($result->failed(), ValidationException::class, [$result->body()]);

        return json_decode($result->body(), true);
    }
}
