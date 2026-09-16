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

class GitlabProvider implements GitProvider
{
    use Outputifier;

    public const DEFAULT_API_URL = 'https://gitlab.com/api/v4';

    public function __construct(public ForgeSetting $setting) {}

    public function name(): string
    {
        return 'GitLab';
    }

    public function createDeployKey(string $title, string $key, bool $readOnly = true): void
    {
        $response = $this->request()->post($this->uri('/projects/%s/deploy_keys'), [
            'title' => $title,
            'key' => $key,
            'can_push' => ! $readOnly,
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
                $this->warning('---> Whoops! No GitLab ID found for the deploy key named: '.$title);

                continue;
            }

            $response = $this->request()->delete(
                $this->uri('/projects/%s/deploy_keys/'.$deployKeyId)
            );

            if ($response->failed()) {
                $this->handleApiErrors($response, 'Deploy key');
            }

            $this->success(sprintf('---> Removed deploy key #%s from GitLab.', $deployKeyId));
        }
    }

    public function comment(string $issueNumber, string $body): void
    {
        $response = $this->request()->post(
            $this->uri('/projects/%s/merge_requests/'.$issueNumber.'/notes'),
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
        $response = $this->request()->get($this->uri('/projects/%s/deploy_keys'));

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
            'Accept' => 'application/json',
            'PRIVATE-TOKEN' => (string) $this->setting->gitToken,
        ]);
    }

    /**
     * GitLab addresses projects by their URL encoded path, so "group/project"
     * has to be sent as "group%2Fproject".
     */
    protected function uri(string $path): string
    {
        $baseUrl = rtrim($this->setting->gitApiUrl ?? self::DEFAULT_API_URL, '/');

        return $baseUrl.sprintf($path, rawurlencode($this->setting->repository));
    }

    protected function handleApiErrors(Response $response, string $apiName): void
    {
        $message = $response->json('message') ?? $response->json('error');

        if (is_array($message)) {
            $message = implode(' ', Arr::flatten($message));
        }

        if (is_string($message) && Str::contains($message, 'has already been taken')) {
            throw ValidationException::withMessages([
                'forbidden' => ['The deploy key is already in use.'],
            ]);
        }

        throw match ($response->status()) {
            404 => ValidationException::withMessages([
                'not_found' => ["{$apiName} could not be found. Check FORGE_GIT_REPOSITORY and GIT_API_URL."],
            ]),
            401 => ValidationException::withMessages([
                'authorization' => ['Unauthorized. Please check your GitLab token.'],
            ]),
            403 => ValidationException::withMessages([
                'forbidden' => ['Forbidden. The token needs api scope with Maintainer access.'],
            ]),
            default => ValidationException::withMessages([
                'api_error' => ['An unexpected error occurred: '.$response->body()],
            ]),
        };
    }
}
