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
use Illuminate\Notifications\Slack\SlackChannel;
use Illuminate\Notifications\Slack\SlackRoute;
use Illuminate\Support\Facades\Notification;

class AnnounceSiteOnSlack
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        // End early if the slack bot token and channel are not set in the Forge service settings
        if ( ! $service->setting->slackBotToken || ! $service->setting->slackChannel ) {
            return $next($service);
        }

//        if ( ! $service->siteNewlyMade ) {
//            $this->information('Skipping Slack announcement as the site is not newly made.');
//            return $next($service);
//        }
//
        $this->information('Announce the site on Slack.');

        var_dump($service->setting->slackChannel, $service->setting->slackBotToken);

        $route = SlackRoute::make($service->setting->slackChannel, env('SLACK_BOT_TOKEN', 'xoxb-3798822736-6739233849188-mtueZKOYSt7EMvYCGLrB2hoQ'));
        Notification::route(SlackChannel::class, $route)
            ->notifyNow(new SiteProvisionedNotification($service));

        return $next($service);
    }
}
