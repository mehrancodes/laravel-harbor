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
            $service->forge->createWorker(
                serverId: $service->server->id,
                siteId: $service->site->id,
                data: $worker
            );
        }

        return $next($service);
    }
}
