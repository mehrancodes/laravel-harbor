<?php

use App\Actions\GenerateDomainName;

it('verify correct domain generation for various branch formats', function ($actual, $expected) {
    expect(
        GenerateDomainName::run(
            $actual['domain'],
            $actual['branch'],
            $actual['pattern'],
        )
    )
        ->toBe($expected);
})
    ->with([
        [
            'actual' => [
                'branch' => 'user_notification+test',
                'pattern' => null,
                'domain' => 'veyoze.com',
            ],
            'expected' => 'user-notification-test.veyoze.com',
        ],
        [
            'actual' => [
                'branch' => 'feature/add-user-notification',
                'pattern' => '/feature\/(.*)/i',
                'domain' => 'veyoze.com',
            ],
            'expected' => 'add-user-notification.veyoze.com',
        ],
        [
            'actual' => [
                'branch' => 'feature/add-user-notification',
                'pattern' => '/feature\/(.*)/i',
                'domain' => 'sub.veyoze.com',
            ],
            'expected' => 'add-user-notification.sub.veyoze.com',
        ],
        [
            'actual' => [
                'branch' => 'feature/add@user-notification',
                'pattern' => '/feature\/(.*)/i',
                'domain' => 'veyoze.com',
            ],
            'expected' => 'add-user-notification.veyoze.com',
        ],
        [
            'actual' => [
                'branch' => 'feature/User-Notification',
                'pattern' => '/feature\/(.*)/i',
                'domain' => 'veyoze.com',
            ],
            'expected' => 'user-notification.veyoze.com',
        ],
        [
            'actual' => [
                'branch' => '360-add-user-notification',
                'pattern' => '/\d+/i',
                'domain' => 'veyoze.com',
            ],
            'expected' => '360.veyoze.com',
        ],
        [
            'actual' => [
                'branch' => 'vyz-145-add-user-notification',
                'pattern' => '/vyz-\d+/i',
                'domain' => 'veyoze.com',
            ],
            'expected' => 'vyz-145.veyoze.com',
        ],
        [
            'actual' => [
                'branch' => 'feature/user@notification!',
                'pattern' => '/feature\/(.*)/i',
                'domain' => 'veyoze.com',
            ],
            'expected' => 'user-notification.veyoze.com',
        ],
    ]);

it('ensure graceful handling of invalid or problematic branch formats', function ($actual, $expected) {
    expect(
        GenerateDomainName::run(
            $actual['domain'],
            $actual['branch'],
            $actual['pattern'],
        )
    )
        ->toBe($expected);
})
    ->with([
        [
            'actual' => [
                'branch' => 'feature/user-notification',
                'pattern' => '/feature\/(.*)/i',
                'domain' => 'veyoze',
            ],
            'expected' => 'user-notification.veyoze',
        ],
    ]);
