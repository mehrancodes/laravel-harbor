<?php

declare(strict_types=1);

namespace App\Services\Forge\Pipeline;

use App\Actions\LineBreaksToArray;
use App\Actions\ParseQueueCommands;
use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;
use Closure;

class CreateQueueWorkers
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (! $service->setting->queueWorkers || ! $service->siteNewlyMade) {
            return $next($service);
        }

        $workers = ParseQueueCommands::run(
            LineBreaksToArray::run($service->setting->queueWorkers),
        );

        $this->information('Creating queue workers.');

        foreach ($workers as $worker) {
            $service->createDaemon([
                'name' => sprintf('Queue worker (%s)', $worker['connection'] ?? 'default'),
                'command' => $this->buildQueueWorkerCommand($worker),
                'user' => $service->site->username,
                'directory' => $worker['directory'] ?? $service->siteDirectory(),
                'processes' => (int) ($worker['numprocs'] ?? 1),
                'startsecs' => 0,
                'stopwaitsecs' => (int) ($worker['stopwaitsecs'] ?? 10),
                'stopsignal' => 'SIGTERM',
            ]);
        }

        return $next($service);
    }

    protected function buildQueueWorkerCommand(array $worker): string
    {
        $binary = $worker['php_version'] ?? 'php';
        $verb = ! empty($worker['daemon']) ? 'queue:work' : 'queue:listen';
        $command = [$binary, 'artisan', $verb, $worker['connection']];

        foreach (['queue', 'timeout', 'sleep', 'delay', 'tries', 'environment', 'memory'] as $option) {
            if (! isset($worker[$option])) {
                continue;
            }

            $command[] = sprintf('--%s=%s', $option, $worker[$option]);
        }

        if (! empty($worker['force'])) {
            $command[] = '--force';
        }

        return implode(' ', $command);
    }
}
