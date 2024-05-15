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

namespace App\Services\Forge;

use Laravel\Forge\Forge;
use Laravel\Forge\Resources\SiteCommand;
use Illuminate\Support\Sleep;

class ForgeSiteCommandWaiter
{
    /**
     * The number of seconds to wait between querying Forge for the command status.
     */
    public int $retrySeconds = 10;

    /**
     * The number of attempts to make before returning the command.
     */
    public int $maxAttempts = 60;

    /**
     * The current number of attempts.
     */
    protected int $attempts = 0;

    public function __construct(public Forge $forge)
    {
    }

    public function waitFor(SiteCommand $site_command): SiteCommand
    {
        $this->attempts = 0;

        while (
            $this->commandIsRunning($site_command)
            && $this->attempts++ < $this->maxAttempts
        ) {
            Sleep::for($this->retrySeconds)->seconds();

            $site_command = $this->forge->getSiteCommand(
                $site_command->serverId,
                $site_command->siteId,
                $site_command->id
            );
        }

        return $site_command;
    }

    protected function commandIsRunning(SiteCommand $site_command): bool
    {
        return !isset($site_command->status)
            || in_array($site_command->status, ['running', 'waiting']);
    }

}
