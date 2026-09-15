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

use App\Services\Forge\Api\ForgeClient;
use App\Services\Forge\Data\ForgeSiteCommandData;
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

    public function __construct(public ForgeClient $client)
    {
        //
    }

    public function waitFor(ForgeSiteCommandData $siteCommand): ForgeSiteCommandData
    {
        $this->attempts = 0;

        while (
            $this->commandIsRunning($siteCommand)
            && $this->attempts++ < $this->maxAttempts
        ) {
            Sleep::for($this->retrySeconds)->seconds();

            $siteCommand = $this->client->getSiteCommand(
                $siteCommand->serverId,
                $siteCommand->siteId,
                $siteCommand->id
            );
        }

        if ($siteCommand->output === null) {
            $siteCommand->output = $this->client->getSiteCommandOutput(
                $siteCommand->serverId,
                $siteCommand->siteId,
                $siteCommand->id
            );
        }

        return $siteCommand;
    }

    protected function commandIsRunning(ForgeSiteCommandData $siteCommand): bool
    {
        return ! isset($siteCommand->status)
            || in_array($siteCommand->status, ['running', 'waiting'], true);
    }
}
