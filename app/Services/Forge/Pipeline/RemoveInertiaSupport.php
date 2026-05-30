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

            $service->deleteDaemon($daemon->id);
        }

        return $next($service);
    }

    protected function getInertiaDaemon(ForgeService $service): ?object
    {
        $daemons = $service->daemons();
        $command = 'php artisan inertia:start-ssr';

        return Arr::first(
            $daemons,
            fn ($daemon) => $daemon->directory == $service->siteDirectory() && $daemon->command == $command
        );
    }
}
