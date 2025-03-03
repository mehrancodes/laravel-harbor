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

class EnsureJobScheduled
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if ($service->setting->jobSchedulerRequired) {
            $this->setupJobIfRequired($service);
        }

        return $next($service);
    }

    private function setupJobIfRequired(ForgeService $service): void
    {
        $command = $this->buildScheduledJobCommand($service->site->username, $service->site->name);

        foreach ($service->forge->jobs($service->server->id) as $job) {
            if ($job->command === $command) {
                $this->information('Scheduler job is already in place.');

                return;
            }
        }

        $this->information('Creating a new scheduler job.');
        $service->forge->createJob($service->server->id, [
            'command' => $command,
            'frequency' => 'minutely',
            'user' => $service->site->username,
        ]);
    }

    protected function buildScheduledJobCommand(string $username, string $domain): string
    {
        return sprintf('php /home/%s/%s/artisan schedule:run', $username, $domain);
    }
}
