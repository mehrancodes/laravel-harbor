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

class EnableInertiaSupport
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (! $service->setting->inertiaSsrEnabled) {
            return $next($service);
        }

        if (! $service->siteNewlyMade) {
            return $next($service);
        }

        $this->addDaemonToStartInertiaSsr($service);

        $this->addCommandToStopInertiaOnReDeploy($service);

        return $next($service);
    }

    protected function addDaemonToStartInertiaSsr(ForgeService $service): void
    {
        $this->information('Create a daemon for Inertia.js SSR.');

        $service->forge->createDaemon($service->server->id, [
            'command' => 'php artisan inertia:start-ssr',
            'user' => 'forge',
            'directory' => $service->siteDirectory()
        ]);
    }

    protected function addCommandToStopInertiaOnReDeploy(ForgeService $service): void
    {
        $script = $service->forge->siteDeploymentScript($service->server->id, $service->site->id);

        if (!str_contains($script, $command = 'php artisan inertia:stop-ssr')) {
            $this->information('Including stop command for Inertia SSR in deploy script.');

            $service->forge->updateSiteDeploymentScript(
                $service->server->id,
                $service->site->id,
                $script . "\n\n$command"
            );
        }
    }
}
