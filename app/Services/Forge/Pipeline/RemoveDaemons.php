<?php

declare(strict_types=1);

namespace App\Services\Forge\Pipeline;

use App\Actions\LineBreaksToArray;
use App\Actions\ParseDaemonCommands;
use App\Services\Forge\ForgeService;
use App\Services\Forge\ForgeSetting;
use App\Traits\Outputifier;
use Closure;
use Illuminate\Support\Str;
use Laravel\Forge\Resources\Daemon;

class RemoveDaemons
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        $daemons = $service->forge->daemons($service->server->id);

        $this->information('Deleting daemons');

        foreach ($daemons as $daemon) {
            // If the daemon is running under the same user as the site in user isolation mode.
            if ($service->setting->siteIsolationRequired && $daemon->user === $service->site->username) {
                $service->forge->deleteDaemon($service->server->id, $daemon->id);
                $this->information("--> Deleted daemon under user: {$daemon->command}");

                continue;
            }

            // If the daemon is running from the same directory as the site.
            if (Str::contains(haystack: $daemon->directory, needles: $service->siteDirectory())) {
                $service->forge->deleteDaemon($service->server->id, $daemon->id);
                $this->information("--> Deleted daemon under directory: {$daemon->command}");

                continue;
            }

            // If a daemon can be detected as being one that was configured by us.
            if ($this->daemonWasAddedForThisSite($daemon, $service->setting)) {
                $service->forge->deleteDaemon($service->server->id, $daemon->id);
                $this->information("--> Deleted daemon created with site: {$daemon->command}");
            }
        }

        return $next($service);
    }

    protected function daemonWasAddedForThisSite(Daemon $daemon, ForgeSetting $setting): bool
    {
        if (empty($setting->daemons)) {
            return false;
        }

        $configuredDaemons = ParseDaemonCommands::run(
            LineBreaksToArray::run($setting->daemons),
        );

        return collect($configuredDaemons)
            ->whereNotNull('user')
            ->whereNotNull('directory')
            ->contains(
                fn (array $configuredDaemon) => $configuredDaemon['command'] === $daemon->command
                    && $configuredDaemon['user'] === $daemon->user
                    && $configuredDaemon['directory'] === $daemon->directory
            );
    }
}
