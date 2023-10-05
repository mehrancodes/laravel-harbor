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
        if (empty($service->setting->envKeys)) {
            return $next($service);
        }

        if ($mergedString = $this->getBothEnvsMerged($service)) {
            $service->forge->updateSiteEnvironmentFile(
                $service->server->id,
                $service->site->id,
                $mergedString
            );
        }

        return $next($service);
    }

    protected function getBothEnvsMerged(ForgeService $service): ?string
    {
        $predefinedVars = array_merge(TextToArray::run($service->setting->envKeys), $service->database);

        if (empty($predefinedVars)) {
            return null;
        }

        $this->information('Processing update of environment variables.');

        $source = TextToArray::run(
            $service->forge->siteEnvironmentFile($service->server->id, $service->site->id)
        );

        return ArrayToText::run(
            array_merge($source, $predefinedVars)
        );
    }
}
