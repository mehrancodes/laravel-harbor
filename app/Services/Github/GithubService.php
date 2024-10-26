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

namespace App\Services\Github;

use App\Services\Forge\ForgeSetting;
use Illuminate\Support\Facades\Http;
use Laravel\Forge\Exceptions\ValidationException;

class GithubService
{
    private const API_ACCEPT = 'application/vnd.github+json';

    private const API_VERSION = '2022-11-28';

    private const API_BASE_URL = 'https://api.github.com';

    public function __construct(public ForgeSetting $setting)
    {
    }

    public function putCommentOnGithubPullRequest(string $body): array
    {
        $uri = sprintf(
            self::API_BASE_URL.'/repos/%s/issues/%s/comments',
            $this->setting->repository,
            $this->setting->gitIssueNumber
        );

        $result = Http::withHeaders([
            'accepts' => self::API_ACCEPT,
            'X-GitHub-Api-Version' => self::API_VERSION,
            'Authorization' => sprintf('Bearer %s', $this->setting->gitToken),
        ])->post($uri, ['body' => $body]);

        throw_if($result->failed(), ValidationException::class, [$result->body()]);

        return json_decode($result->body(), true);
    }

    public function createDeployKey(string $title, string $key, bool $readonly = true): array
    {
        $uri = sprintf(
            self::API_BASE_URL.'/repos/%s/keys',
            $this->setting->repository
        );

        $result = Http::withHeaders([
            'accepts' => self::API_ACCEPT,
            'X-GitHub-Api-Version' => self::API_VERSION,
            'Authorization' => sprintf('Bearer %s', $this->setting->gitToken),
        ])->post($uri, [
            'title' => $title,
            'key' => $key,
            'readonly' => $readonly,
        ]);

        throw_if($result->failed() && ! in_array('key is already in use', $result->json('errors.*.message', [])), ValidationException::class, [$result->body()]);

        return json_decode($result->body(), true);
    }
}
