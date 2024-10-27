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
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

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

        if ($result->failed()) {
            $this->handleApiErrors($result, 'Comment');
        }

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

        if ($result->failed()) {
            $this->handleApiErrors($result, 'Deploy key');
        }

        return json_decode($result->body(), true);
    }

    protected function handleApiErrors(Response $response, $apiName): void
    {
        // Extract all error messages from the response
        $errorMessages = $response->json('errors.*.message', []);

        // Check if the specific error 'key is already in use' exists, used when creating a deploy key
        $isDeployKeyInUse = in_array('key is already in use', $errorMessages);

        if ($isDeployKeyInUse) {
            throw ValidationException::withMessages([
                'forbidden' => ['The deploy key is already in use.'],
            ]);
        }

        // Handle other specific status codes if needed
        throw match ($response->status()) {
            404 => ValidationException::withMessages([
                'not_found' => ["{$apiName} could not be found."],
            ]),
            401 => ValidationException::withMessages([
                'authorization' => ['Unauthorized. Please check your GitHub token.'],
            ]),
            403 => ValidationException::withMessages([
                'forbidden' => ['Forbidden. You might not have the necessary permissions.'],
            ]),
            default => ValidationException::withMessages([
                'api_error' => ['An unexpected error occurred: ' . $response->body()],
            ]),
        };
    }
}
