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

namespace App\Services\Forge\Actions;

use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;

class RecreateDatabase
{
    use Outputifier;

    /**
     * Handle database recreation and creation process
     * 
     * @param ForgeService $service The forge service
     * @param string $dbName The database name
     * @param string $dbPassword Password for database creation
     * @return bool True if database needs to be updated in the env file
     */
    public function handle(ForgeService $service, string $dbName, string $dbPassword): bool
    {
        $databaseId = null;
        foreach ($service->forge->databases($service->server->id) as $database) {
            if (isset($database->name) && $database->name === $dbName) {
                $databaseId = $database->id;
            }
        }

        // If the setting is not enabled, we skip the deletion of existing databases.
        if (isset($databaseId) && !$service->setting->forceDeleteOldDatabase) {
            $this->warning('It seems there is an existing database with the same name. Skipping database creation. Ensure to update the database password in the .env file manually.');
            return true; // Still need to update env vars even if we don't create DB
        }

        $this->information('Force deleting existing matched database and user if found.');

        if (isset($databaseId)) {
            $service->forge->deleteDatabase($service->server->id, $databaseId);
            $this->information('---> Existing database deleted: ' . $dbName);
        }

        foreach ($service->forge->databaseUsers($service->server->id) as $user) {
            if (isset($user->name) && $user->name === $dbName) {
                $service->forge->deleteDatabaseUser($service->server->id, $user->id);
                $this->information('---> Existing database user found and deleted: ' . $dbName);
            }
        }

        if (isset($databaseId)) {
            $this->information('---> Waiting for the database deletion to complete...');
            $this->waitForDatabaseDeletion();
        }
        
        // Create the database with the provided password
        $this->information('Creating database.');
        $this->createDatabase($service, $dbName, $dbPassword);

        return true;
    }

    /**
     * Wait for database deletion to complete
     */
    protected function waitForDatabaseDeletion(): void
    {
        sleep(6);
    }
    
    /**
     * Create a new database on the server
     */
    public function createDatabase(ForgeService $service, string $dbName, string $dbPassword): void
    {
        $service->forge->createDatabase($service->server->id, [
            'name' => $dbName,
            'user' => $dbName,
            'password' => $dbPassword,
        ]);
        
        $this->information('Database created: ' . $dbName);
    }
}
