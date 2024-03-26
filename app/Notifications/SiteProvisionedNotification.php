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

namespace App\Notifications;

use App\Services\Forge\ForgeService;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackChannel;
use Illuminate\Notifications\Slack\SlackMessage;

class SiteProvisionedNotification extends Notification
{
    public function __construct(protected ForgeService $service)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return [SlackChannel::class];
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        return (new SlackMessage)
            ->to($this->service->setting->slackChannel)
            ->text('A new site has been provisioned!')
            ->sectionBlock(function (SectionBlock $block) {
                $block->text('A preview site has been deployed with Laravel Harbor');

                $block->field("*Branch Name:*\n{$this->service->setting->branch}")->markdown();
                $block->field("*Environment URL:*\n{$this->service->getSiteLink()}")->markdown();
            })
            ->dividerBlock()
            ->sectionBlock(function (SectionBlock $block) {
                $block->text('Congratulations!');
            });
    }
}
