<?php

declare(strict_types=1);

/**
 * This file is part of Veyoze CLI.
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
use Laravel\Forge\Resources\DatabaseUser;

class RemoveDatabaseUser
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        foreach ($service->forge->databaseUsers($service->setting->server) as $databaseUser) {
            if ($databaseUser->name === $service->generateDatabaseName()) {
                $this->information('Removing database with user.');

                foreach ($databaseUser->databases as $database) {
                    $service->forge->deleteDatabase($service->setting->server, $database);
                }

                $databaseUser->delete();
            }
        }

        return $next($service);
    }
}
