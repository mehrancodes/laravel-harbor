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

use App\Actions\FormattedBranchName;
use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;
use Closure;
use Illuminate\Support\Str;

class CreateDatabase
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (! $service->setting->dbCreationRequired || ! $service->siteNewlyMade) {
            return $next($service);
        }

        if ( $service->setting->dbName ) {
            $dbName = FormattedBranchName::run($service->setting->dbName);
        } else {
            $dbName = $service->getStandardizedBranchName();
        }

        $dbPassword = Str::random(16);

        if (! $this->databaseExists($service, $dbName)) {
            $this->information('Creating database.');

            $this->createDatabase($service, $dbName, $dbPassword);
        }

        $service->setDatabase([
            'DB_DATABASE' => $dbName,
            'DB_USERNAME' => $dbName,
            'DB_PASSWORD' => $dbPassword,
        ]);

        return $next($service);
    }

    protected function databaseExists(ForgeService $service, string $dbName): bool
    {
        foreach ($service->forge->databases($service->server->id) as $database) {
            if ($database->name === $dbName) {
                return true;
            }
        }

        return false;
    }

    protected function createDatabase(ForgeService $service, string $dbName, string $dbPassword): void
    {
        $service->forge->createDatabase($service->server->id, [
            'name' => $dbName,
            'user' => $dbName,
            'password' => $dbPassword,
        ]);
    }
}
