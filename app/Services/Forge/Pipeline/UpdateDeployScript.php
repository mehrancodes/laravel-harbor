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

class UpdateDeployScript
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (empty($service->setting->deployScript)) {
            return $next($service);
        }

        $this->information('Updating deployment script.');

        $service->forge->updateSiteDeploymentScript(
            $service->server->id,
            $service->site->id,
            $service->setting->deployScript,
            $service->setting->autoSourceRequired
        );

        return $next($service);
    }
}
