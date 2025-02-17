<?php

declare(strict_types=1);

namespace App\Services\Forge\Pipeline;

use App\Actions\LineBreaksToArray;
use App\Actions\ParseDaemonCommands;
use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;
use Closure;

class CreateDaemons
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (! $service->setting->daemons || ! $service->siteNewlyMade) {
            return $next($service);
        }

        $daemons = ParseDaemonCommands::run(
            LineBreaksToArray::run($service->setting->daemons),
        );

        $this->information('Creating daemons.');

        foreach ($daemons as $daemon) {
            $service->forge->createDaemon(
                serverId: $service->server->id,
                data: array_merge(
                    [
                        // Defaults a daemon to run under the same user and directory as the current site.
                        'user' => $service->site->username,
                        'directory' => $service->siteDirectory(),
                    ],
                    $daemon
                )
            );
        }

        return $next($service);
    }
}
