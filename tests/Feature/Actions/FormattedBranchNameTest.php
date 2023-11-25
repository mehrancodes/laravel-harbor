<?php

use App\Actions\FormattedBranchName;

it('verify correct domain generation for various branch formats', function ($actual, $expected) {
    expect(
        FormattedBranchName::run(
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
            ],
            'expected' => 'user-notification-test',
        ],
        [
            'actual' => [
                'branch' => 'feature/add-user-notification',
                'pattern' => '/feature\/(.*)/i',
            ],
            'expected' => 'add-user-notification',
        ],
        [
            'actual' => [
                'branch' => 'feature/add-user-notification',
                'pattern' => '/feature\/(.*)/i',
            ],
            'expected' => 'add-user-notification',
        ],
        [
            'actual' => [
                'branch' => 'feature/add@user-notification',
                'pattern' => '/feature\/(.*)/i',
            ],
            'expected' => 'add-user-notification',
        ],
        [
            'actual' => [
                'branch' => 'feature/User-Notification',
                'pattern' => '/feature\/(.*)/i',
            ],
            'expected' => 'user-notification',
        ],
        [
            'actual' => [
                'branch' => '360-add-user-notification',
                'pattern' => '/\d+/i',
            ],
            'expected' => '360',
        ],
        [
            'actual' => [
                'branch' => 'vyz-145-add-user-notification',
                'pattern' => '/vyz-\d+/i',
            ],
            'expected' => 'vyz-145',
        ],
        [
            'actual' => [
                'branch' => 'feature/user@notification!',
                'pattern' => '/feature\/(.*)/i',
            ],
            'expected' => 'user-notification',
        ],
    ]);

it('ensure graceful handling of invalid or problematic branch formats', function ($actual, $expected) {
    expect(
        FormattedBranchName::run(
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
            ],
            'expected' => 'user-notification',
        ],
    ]);
