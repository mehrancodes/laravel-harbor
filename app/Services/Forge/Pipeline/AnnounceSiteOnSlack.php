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

use App\Notifications\SiteProvisionedNotification;
use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;
use Closure;
use Illuminate\Support\Facades\Notification;

class AnnounceSiteOnSlack
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        // End early if the slack bot token and channel are not set in the Forge service settings
        if ( empty( $service->setting->slackBotToken ) || empty( $service->setting->slackChannel ) ) {
            $this->information('Slack Bot Token ' . $service->setting->slackBotToken . ' and Slack Channel ' . $service->setting->slackChannel . ' are not set. Skipping Slack announcement.');
            return $next($service);
        }

        $this->information('Announce the site on Slack.');

        Notification::route('slack', null)->notify(new SiteProvisionedNotification($service));

        return $next($service);
    }
}
