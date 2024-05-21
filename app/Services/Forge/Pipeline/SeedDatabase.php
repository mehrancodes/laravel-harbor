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

class SeedDatabase
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (!($seeder = $service->setting->dbSeed)) {
            return $next($service);
        }

        if (!$service->siteNewlyMade) {
            return $next($service);
        }

        return $this->attemptSeed($service, $next, $seeder);
    }

    public function attemptSeed(ForgeService $service, Closure $next)
    {
        $this->information(sprintf('Seeding database.'));

        $content = $this->buildImportCommandContent($service);

        $site_command = $service->waitForSiteCommand(
            $service->forge->executeSiteCommand(
                $service->setting->server,
                $service->site->id,
                ['command' => $content]
            )
        );

        if ($site_command->status === 'failed') {
            $this->fail(sprintf('---> Database seed failed with message: %s', $site_command->output));
            return $next;

        } elseif ($site_command->status !== 'finished') {
            $this->fail('---> Database seed did not finish in time.');
            return $next;
        }

        return $next($service);
    }

    public function buildImportCommandContent(ForgeService $service): string
    {
        $seeder = '';
        if (is_string($service->setting->dbSeed)) {
            $seeder = sprintf(
                '--class=%s',
                $service->setting->dbSeed
            );
        }

        return trim(sprintf(
            '%s artisan db:seed %s',
            $this->phpExecutable($service->site->phpVersion ?? 'php'),
            $seeder
        ));
    }

    /**
     * Forge's phpVersion strings don't exactly map to the executable.
     * For example php83 corresponds to the php8.3 executable.
     * This workaround assumes no minor versions above 9!
     */
    protected function phpExecutable(string $phpVersion): string
    {
        return preg_replace_callback('/\d$/', fn ($matches) => '.' . $matches[0], $phpVersion);
    }
}
