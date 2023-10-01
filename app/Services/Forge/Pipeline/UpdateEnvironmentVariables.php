<?php

declare(strict_types=1);

/**
 * This file is part of Harbor CLI.
 *
 * (c) Mehran Rasulian <mehran.rasulian@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace App\Services\Forge\Pipeline;

use App\Actions\ArrayToText;
use App\Actions\TextToArray;
use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;
use Closure;

class UpdateEnvironmentVariables
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (empty($service->setting->envVars)) {
            return $next($service);
        }

        $this->information('Processing update of environment variables.');

        $mergedString = $this->getBothEnvsMerged($service);

        $service->forge->updateSiteEnvironmentFile(
            $service->server->id,
            $service->site->id,
            $mergedString
        );

        return $next($service);
    }

    protected function getBothEnvsMerged(ForgeService $service): string
    {
        if ($service->database !== null) {
            $this->information('---> Update database environment variables');
        }

        $predefinedVars = TextToArray::run($service->setting->envVars);

        $source = TextToArray::run(
            $service->forge->siteEnvironmentFile($service->server->id, $service->site->id)
        );

        return ArrayToText::run(
            array_merge($source, $predefinedVars, $service->database)
        );
    }
}
