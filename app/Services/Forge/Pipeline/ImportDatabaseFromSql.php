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

namespace App\Services\Forge\Pipeline;

use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;
use Closure;

class ImportDatabaseFromSql
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (! ($file = $service->setting->dbImportSql)) {
            return $next($service);
        }

        if (! $service->siteNewlyMade && ! $service->setting->dbImportOnDeployment) {
            return $next($service);
        }

        return $this->attemptImport($service, $next, $file);
    }

    public function attemptImport(ForgeService $service, Closure $next, string $file)
    {
        $this->information(sprintf('Importing database from %s.', $file));

        $content = $this->buildImportCommandContent($service, $file);

        $siteCommand = $service->waitForSiteCommand(
            $service->client->runSiteCommand(
                $service->setting->server,
                $service->site->id,
                $content
            )
        );

        if ($siteCommand->status === 'failed') {
            $this->failCommand(sprintf('---> Database import failed with message: %s', $siteCommand->output));

            return $next;
        }

        if ($siteCommand->status !== 'finished') {
            $this->failCommand('---> Database import did not finish in time.');

            return $next;
        }

        return $next($service);
    }

    public function buildImportCommandContent(ForgeService $service, string $file): string
    {
        $extract = match (pathinfo($file, PATHINFO_EXTENSION)) {
            'gz' => "gunzip < {$file}",
            'zip' => "unzip -p {$file}",
            default => "cat {$file}",
        };

        return implode(' ', [
            $extract,
            '|',
            $this->buildDatabaseConnection($service),
        ]);
    }

    protected function buildDatabaseConnection(ForgeService $service): string
    {
        $databaseType = $service->server->databaseType ?? '';

        if (str_contains($databaseType, 'postgres')) {
            return sprintf(
                'psql postgres://%s:%s%s/%s',
                $service->database['DB_USERNAME'],
                $service->database['DB_PASSWORD'],
                isset($service->database['DB_HOST'])
                    ? sprintf(
                        '@%s:%s',
                        $service->database['DB_HOST'],
                        $service->database['DB_PORT'] ?? '5432',
                    )
                    : '',
                $service->getFormattedDatabaseName(),
            );
        }

        return sprintf(
            '%s -u %s -p%s %s %s %s',
            str_contains($databaseType, 'mariadb') ? 'mariadb' : 'mysql',
            $service->database['DB_USERNAME'],
            $service->database['DB_PASSWORD'],
            isset($service->database['DB_HOST']) ? '-h '.$service->database['DB_HOST'] : '',
            isset($service->database['DB_PORT']) ? '-P '.$service->database['DB_PORT'] : '',
            $service->getFormattedDatabaseName(),
        );
    }
}
