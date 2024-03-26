<?php

use App\Rules\BranchNameRegex;
use Illuminate\Support\Facades\Validator;

test('it validates the subdomain pattern matches the branch name', function ($data, $failed) {
    $validator = Validator::make($data, [
        'branch' => ['required', new BranchNameRegex],
        'subdomain_pattern' => ['string'],
    ]);

    expect($validator->fails())->toBe($failed);
})
    ->with([
        [
            'actual' => [
                'branch' => 'user-notification',
                'subdomain_pattern' => '/feature\/(.+)/i',
            ],
            'failed' => true,
        ],
        [
            'actual' => [
                'branch' => 'feature/user-notification',
                'subdomain_pattern' => '/feature\/(.+)/i',
            ],
            'failed' => false,
        ],
        [
            'actual' => [
                'branch' => 'feature/user-notification',
                'subdomain_pattern' => '',
            ],
            'failed' => false,
        ],
    ]);
