<?php

use App\Actions\LineBreaksToArray;
use App\Actions\TextToArray;

it('converts string into array items by line breaks', function ($actual, $expected) {
    expect(
        LineBreaksToArray::run($actual)
    )
        ->toBe($expected);
})
    ->with([
        [
            "First line\nSecond line",
            [
                'First line',
                'Second line',
            ],
        ],
        [
            "\nFirst line\n\nSecond line\n",
            [
                'First line',
                'Second line',
            ],
        ],
        [
            " \nOnly 2 lines\n3rd line has invisible space\n‎ ",
            [
                'Only 2 lines',
                '3rd line has invisible space',
            ],
        ],
        [
            null,
            [],
        ],
        [
            '',
            [],
        ],
    ]);
