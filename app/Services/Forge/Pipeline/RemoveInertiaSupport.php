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
use Illuminate\Support\Arr;
use Laravel\Forge\Resources\Daemon;

class RemoveInertiaSupport
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (! $service->setting->inertiaSsrEnabled) {
            return $next($service);
        }

        if ($daemon = $this->getInertiaDaemon($service)) {
            $this->information('Removing the daemon for Inertia.js SSR command.');

            $service->forge->deleteDaemon($service->server->id, $daemon->id);
        }

        return $next($service);
    }

    protected function getInertiaDaemon(ForgeService $service): ?Daemon
    {
        $daemons = $service->forge->daemons($service->server->id);
        $command = 'php artisan inertia:start-ssr';

        return Arr::first(
            $daemons,
            fn ($daemon) => $daemon->directory == $service->siteDirectory() && $daemon->command == $command
        );
    }
}
