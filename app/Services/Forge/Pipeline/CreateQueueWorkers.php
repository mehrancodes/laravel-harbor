<?php

declare(strict_types=1);

namespace App\Services\Forge\Pipeline;

use App\Actions\ParseQueueCommands;
use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;
use Closure;
use Illuminate\Support\Str;

class CreateQueueWorkers
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (! $service->setting->queueWorkers || ! $service->siteNewlyMade) {
            return $next($service);
        }

        $workers = ParseQueueCommands::run(
            str($service->setting->queueWorkers)
                ->explode("\n")
                ->map(Str::squish(...))
                ->filter()
                ->values()
                ->all()
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
