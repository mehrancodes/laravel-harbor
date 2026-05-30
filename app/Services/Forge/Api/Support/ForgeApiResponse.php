<?php

declare(strict_types=1);

namespace App\Services\Forge\Api\Support;

use App\Services\Forge\Api\Exceptions\ForgeApiException;
use App\Services\Forge\Api\Exceptions\ForgeValidationException;
use Saloon\Http\Response;

class ForgeApiResponse
{
    public static function payload(Response $response): array
    {
        if (! $response->successful()) {
            static::throwFrom($response);
        }

        $body = trim($response->body());

        if ($body === '') {
            return [];
        }

        return $response->json();
    }

    protected static function throwFrom(Response $response): never
    {
        $payload = $response->json();
        $errors = static::extractErrors($payload);

        $message = $errors !== [] ? implode(PHP_EOL, $errors) : (string) ($payload['message'] ?? 'Forge API request failed.');

        if ($response->status() === 422) {
            throw new ForgeValidationException($message, 422, $errors);
        }

        throw new ForgeApiException($message, $response->status(), $errors);
    }

    protected static function extractErrors(array $payload): array
    {
        $errors = $payload['errors'] ?? [];

        if (! is_array($errors) || $errors === []) {
            return [];
        }

        // JSON:API format: errors: [{ detail, title, ... }]
        if (array_is_list($errors)) {
            return collect($errors)
                ->map(function (mixed $error): string {
                    if (! is_array($error)) {
                        return 'Unknown API error';
                    }

                    return (string) ($error['detail'] ?? $error['title'] ?? 'Unknown API error');
                })
                ->filter()
                ->values()
                ->all();
        }

        // Laravel validation format: errors: { field: [messages...] }
        return collect($errors)
            ->flatMap(fn (mixed $messages, string $field) => collect((array) $messages)
                ->map(fn (mixed $message) => sprintf('%s: %s', $field, (string) $message)))
            ->filter()
            ->values()
            ->all();
    }
}
