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
    protected const SCHEDULER_JOB_PREFIX = 'Harbor scheduler';

    public function __invoke(ForgeService $service, Closure $next)
    {
        if ($service->setting->jobSchedulerRequired) {
            $this->setupJobIfRequired($service);
        }

        return $next($service);
    }

    private function setupJobIfRequired(ForgeService $service): void
    {
        $jobName = $this->buildScheduledJobName($service->site->name);
        $command = $this->buildScheduledJobCommand($service->site->username, $service->site->name);

        foreach ($service->jobs() as $job) {
            if ($job->name === $jobName && $job->command === $command) {
                $this->information('Scheduler job is already in place.');

                return;
            }
        }

        $this->information('Creating a new scheduler job.');
        $service->createJob([
            'name' => $jobName,
            'command' => $command,
            'frequency' => 'minutely',
            'user' => $service->site->username,
        ]);
    }

    protected function buildScheduledJobName(string $siteName): string
    {
        return sprintf('%s %s', self::SCHEDULER_JOB_PREFIX, $siteName);
    }

    protected function buildScheduledJobCommand(string $username, string $domain): string
    {
        return sprintf('php /home/%s/%s/artisan schedule:run', $username, $domain);
    }
}
