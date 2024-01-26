<?php

use App\Actions\SearchReplaceNginxTemplateVariables;

it('it can search and replace the given variables', function ($actual, $expected) {
    expect(
        SearchReplaceNginxTemplateVariables::run(
            $actual['variable'],
            $actual['template'],
        )
    )
        ->toBe($expected);
})
    ->with([
        [
            'actual' => [
                'variable' => ['CUSTOM_PORT' => 1234],
                'template' => 'port={{CUSTOM_PORT}}',
            ],
            'expected' => 'port=1234',
        ],
    ]);
