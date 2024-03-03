<?php

namespace App\Notifications;

use App\Services\Forge\ForgeService;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackChannel;
use Illuminate\Notifications\Slack\SlackMessage;
use Illuminate\Notifications\Notification;

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
                $block->text('An invoice has been paid.');

                $block->field("*Branch Name URL:*\n{$this->service->setting->branch}")->markdown();
                $block->field("*Environment URL:*\n{$this->service->getSiteLink()}")->markdown();
            })
            ->dividerBlock()
            ->sectionBlock(function (SectionBlock $block) {
                $block->text('Congratulations!');
            });
    }
}
