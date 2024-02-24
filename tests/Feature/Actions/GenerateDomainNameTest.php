<?php

use App\Actions\GenerateDomainName;

it('verify correct domain generation for various branch formats', function ($actual, $expected) {
    expect(
        GenerateDomainName::run(
            $actual['domain'],
            $actual['branch'],
        )
    )
        ->toBe($expected);
})
    ->with([
        [
            'actual' => [
                'branch' => 'user-notification-test',
                'domain' => 'harbor.com',
            ],
            'expected' => 'user-notification-test.harbor.com',
        ],
        [
            'actual' => [
                'branch' => 'user-notification',
                'domain' => 'sub.harbor.com',
            ],
            'expected' => 'user-notification.sub.harbor.com',
        ],
    ]);
