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

use App\Actions\MergeEnvironmentVariables;
use App\Actions\TextToArray;
use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;
use Closure;

class UpdateEnvironmentVariables
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        $newKeys = array_merge(TextToArray::run($service->setting->envKeys), $service->database);

        if (! empty($newKeys)) {
            $this->information('Processing update of environment variables.');

            $service->forge->updateSiteEnvironmentFile(
                $service->server->id,
                $service->site->id,
                $this->getBothEnvsMerged($service, $newKeys)
            );
        }

        return $next($service);
    }

    protected function getBothEnvsMerged(ForgeService $service, array $newKeys): string
    {
        $source = $service->forge->siteEnvironmentFile($service->server->id, $service->site->id);

        return MergeEnvironmentVariables::run($source, $newKeys);
    }
}
