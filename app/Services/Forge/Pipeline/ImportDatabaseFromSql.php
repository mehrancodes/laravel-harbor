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
        if (!$service->site->siteNewlyMade && !$service->setting->dbImportOnDeployment) {
            return $next($service);
        }

        if (!($file = $service->setting->dbImportSql)) {
            return $next($service);
        }

        return $this->attemptImport($service, $next, $file);
    }

    public function attemptImport(ForgeService $service, Closure $next, string $file)
    {
        $this->information(sprintf('Importing database from %s.', $file));

        $content = $this->buildImportCommandContent($service, $file);
        
        $site_command = $service->waitForSiteCommand(
            $service->forge->executeSiteCommand(
                $service->setting->server,
                $service->site->id,
                ['command' => $content]
            )
        );

        if ($site_command->status === 'failed') {
            $this->fail(sprintf('---> Database import command failed with message: %s', $site_command->output));
            return $next;

        } else if ($site_command->status !== 'finished') {
            $this->fail('---> Database import command did not finish in time.');
            return $next;
        }

        $this->information('---> Database import command finished successfully.');

        return $next($service);
    }

    public function buildImportCommandContent(ForgeService $service, string $file): string
    {
        $extract = match(pathinfo($file, PATHINFO_EXTENSION)) {
            'gz'    => "gunzip < {$file}",
            'zip'   => "unzip -p {$file}",
            default => "cat {$file}"
        };

        return collect([
            $extract,
            '|',
            'mysql',
            '-u',
            $service->database['DB_USERNAME'],
            "-p{$service->database['DB_PASSWORD']}",
            '-P',
            $service->database['DB_PORT'],
            isset($service->database['DB_HOST']) ? '-h ' . $service->database['DB_HOST'] : '',
            $service->getFormattedDatabaseName(),
        ])
            ->filter()
            ->implode(' ');
    }
}