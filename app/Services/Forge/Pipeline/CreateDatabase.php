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

use App\Services\Forge\Actions\RecreateDatabase;
use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;
use Closure;
use Illuminate\Support\Str;

class CreateDatabase
{
    use Outputifier;
    
    private RecreateDatabase $recreateDatabase;

    public function __construct(RecreateDatabase $recreateDatabase = null)
    {
        $this->recreateDatabase = $recreateDatabase ?? new RecreateDatabase();
    }
    
    public function __invoke(ForgeService $service, Closure $next)
    {
        if (! $service->setting->dbCreationRequired || ! $service->siteNewlyMade) {
            return $next($service);
        }

        $dbName = $service->getFormattedDatabaseName();
        $dbPassword = Str::random(16);
        
        // Handle DB recreation and creation in one call
        $this->recreateDatabase->handle($service, $dbName, $dbPassword);

        $service->setDatabase([
            'DB_DATABASE' => $dbName,
            'DB_USERNAME' => $dbName,
            'DB_PASSWORD' => $dbPassword,
        ]);

        return $next($service);
    }




}
