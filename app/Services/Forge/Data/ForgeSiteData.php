<?php

declare(strict_types=1);

namespace App\Services\Forge\Data;

use App\Services\Forge\Api\Support\JsonApiData;
use Illuminate\Support\Str;

class ForgeSiteData
{
    public function __construct(
        public int|string $id,
        public string $name,
        public ?string $status,
        public string $username,
        public ?string $repository,
        public ?string $webDirectory,
        public ?string $rootDirectory,
        public ?string $directory,
        public ?string $deploymentUrl,
    ) {
        //
    }

    public static function fromResource(array $resource): self
    {
        $attributes = JsonApiData::attributes($resource);
        $webDirectory = $attributes['web_directory'] ?? null;
        $rootDirectory = $attributes['root_directory'] ?? null;
        $repository = $attributes['repository'] ?? null;

        $directory = null;

        if (is_string($webDirectory) && is_string($rootDirectory)) {
            $directory = Str::replaceFirst($rootDirectory, '', $webDirectory) ?: '/';
        }

        return new self(
            id: JsonApiData::id($resource),
            name: (string) ($attributes['name'] ?? ''),
            status: $attributes['status'] ?? null,
            username: (string) ($attributes['user'] ?? ''),
            repository: self::resolveRepository($repository),
            webDirectory: $webDirectory,
            rootDirectory: $rootDirectory,
            directory: $directory,
            deploymentUrl: $attributes['deployment_url'] ?? null,
        );
    }

    private static function resolveRepository(mixed $repository): ?string
    {
        if (is_string($repository)) {
            return $repository;
        }

        if (is_array($repository)) {
            $url = $repository['url'] ?? null;

            return is_string($url) ? $url : null;
        }

        return null;
    }
}
