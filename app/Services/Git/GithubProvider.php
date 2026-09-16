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

namespace App\Services\Git;

use App\Services\Forge\ForgeSetting;
use App\Services\Git\Contracts\GitProvider;
use App\Traits\Outputifier;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GithubProvider implements GitProvider
{
    use Outputifier;

    private const API_ACCEPT = 'application/vnd.github+json';

    private const API_VERSION = '2022-11-28';

    public const DEFAULT_API_URL = 'https://api.github.com';

    public function __construct(public ForgeSetting $setting) {}

    public function name(): string
    {
        return 'GitHub';
    }

    public function createDeployKey(string $title, string $key, bool $readOnly = true): void
    {
        $response = $this->request()->post($this->uri('/repos/%s/keys', $this->setting->repository), [
            'title' => $title,
            'key' => $key,
            'read_only' => $readOnly,
        ]);

        if ($response->failed()) {
            $this->handleApiErrors($response, 'Deploy key');
        }
    }

    public function deleteDeployKeysByTitle(string $title): void
    {
        $deployKeys = $this->deployKeysByTitle($title);

        $this->information(sprintf('Deploy Keys found for delete: #%s', count($deployKeys)));

        foreach ($deployKeys as $deployKey) {
            if (! $deployKeyId = Arr::get($deployKey, 'id')) {
                $this->warning('---> Whoops! No GitHub ID found for the deploy key named: '.$title);

                continue;
            }

            $response = $this->request()->delete(
                $this->uri('/repos/%s/keys/%s', $this->setting->repository, (string) $deployKeyId)
            );

            if ($response->failed()) {
                $this->handleApiErrors($response, 'Deploy key');
            }

            $this->success(sprintf('---> Removed deploy key #%s from GitHub.', $deployKeyId));
        }
    }

    public function comment(string $issueNumber, string $body): void
    {
        $response = $this->request()->post(
            $this->uri('/repos/%s/issues/%s/comments', $this->setting->repository, $issueNumber),
            ['body' => $body]
        );

        if ($response->failed()) {
            $this->handleApiErrors($response, 'Comment');
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function deployKeysByTitle(string $title): array
    {
        $response = $this->request()->get($this->uri('/repos/%s/keys', $this->setting->repository));

        if ($response->failed()) {
            $this->handleApiErrors($response, 'Deploy key');
        }

        return collect($response->json() ?? [])
            ->filter(fn (array $key) => Str::contains(Arr::get($key, 'title', ''), $title))
            ->values()
            ->all();
    }

    protected function request(): PendingRequest
    {
        return Http::withHeaders([
            'Accept' => self::API_ACCEPT,
            'X-GitHub-Api-Version' => self::API_VERSION,
            'Authorization' => sprintf('Bearer %s', $this->setting->gitToken),
        ]);
    }

    protected function uri(string $path, string ...$parameters): string
    {
        $baseUrl = rtrim($this->setting->gitApiUrl ?? self::DEFAULT_API_URL, '/');

        return $baseUrl.sprintf($path, ...$parameters);
    }

    protected function handleApiErrors(Response $response, string $apiName): void
    {
        $errorMessages = $response->json('errors.*.message', []);

        if (in_array('key is already in use', $errorMessages, true)) {
            throw ValidationException::withMessages([
                'forbidden' => ['The deploy key is already in use.'],
            ]);
        }

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
                'api_error' => ['An unexpected error occurred: '.$response->body()],
            ]),
        };
    }
}
