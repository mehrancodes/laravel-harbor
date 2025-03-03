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

class RemoveTaskScheduler
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        foreach ($service->forge->jobs($service->setting->server) as $job) {
            if ($job->command === sprintf('php /home/%s/%s/artisan schedule:run', $service->site->username, $service->site->name)) {
                $this->information('Removing scheduled command.');

                $job->delete();
            }
        }

        // Wait a few seconds to make sure the scheduler is fully removed before kicking off the next task
        sleep(10);

        return $next($service);
    }
}
