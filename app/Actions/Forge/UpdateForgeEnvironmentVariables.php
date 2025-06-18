<?php

declare(strict_types=1);

namespace App\Actions\Forge;

use App\Actions\MergeEnvironmentVariables;
use App\Actions\TextToArray;
use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;

class UpdateForgeEnvironmentVariables
{
    use Outputifier;

    /**
     * Handle the update of environment variables in a Forge site.
     *
     * @param ForgeService $service
     * @return bool
     */
    public function handle(ForgeService $service): bool
    {
        $newKeys = array_merge(
            TextToArray::run($service->setting->envKeys),
            $service->database
        );

        if ($service->setting->sslRequired) {
            $newKeys = array_merge($newKeys, ['APP_URL' => $service->getSiteLink()]);
        }

        if (empty($newKeys)) {
            return false;
        }

        $source = $service->forge->siteEnvironmentFile($service->server->id, $service->site->id);
        $mergedEnvs = MergeEnvironmentVariables::run($source, $newKeys);

        $service->forge->updateSiteEnvironmentFile(
            $service->server->id,
            $service->site->id,
            $mergedEnvs
        );

        return true;
    }
}
