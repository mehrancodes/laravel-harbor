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

namespace App\Commands;

use App\Services\Forge\ForgeService;
use App\Services\Forge\Pipeline\DestroySite;
use App\Services\Forge\Pipeline\FindServer;
use App\Services\Forge\Pipeline\FindSiteOrFail;
use App\Services\Forge\Pipeline\RemoveDaemons;
use App\Services\Forge\Pipeline\RemoveDatabaseUser;
use App\Services\Forge\Pipeline\RemoveExistingDeployKey;
use App\Services\Forge\Pipeline\RemoveInertiaSupport;
use App\Services\Forge\Pipeline\RemoveTaskScheduler;
use App\Services\Forge\Pipeline\RunOptionalCommands;
use App\Traits\Outputifier;
use Illuminate\Support\Facades\Pipeline;
use LaravelZero\Framework\Commands\Command;

class TearDownCommand extends Command
{
    use Outputifier;

    protected $signature = 'teardown';

    protected $description = 'Removes the provisioned environment.';

    public function handle(ForgeService $service): void
    {
        Pipeline::send($service)
            ->through([
                FindServer::class,
                FindSiteOrFail::class,
                RemoveInertiaSupport::class,
                RunOptionalCommands::class,
                RemoveDatabaseUser::class,
                RemoveExistingDeployKey::class,
                RemoveDaemons::class,
                RemoveTaskScheduler::class,
                DestroySite::class,
            ])
            ->then(fn () => $this->success('Environment teardown successful! All provisioned resources have been removed.'));
    }
}
