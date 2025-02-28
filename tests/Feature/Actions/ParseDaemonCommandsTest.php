<?php

use App\Actions\ParseDaemonCommands;
use Symfony\Component\Console\Output\BufferedOutput;

use function Termwind\renderUsing;

it('extracts api parameters from command syntax', function ($actual, $expected) {
    expect(
        ParseDaemonCommands::run($actual)
    )
        ->toBe($expected);
})
    ->with([
        [
            [
                'php artisan horizon',
                'custom-script --directory=/usr/local/bin',
            ],
            [
                [
                    'command' => 'php artisan horizon',
                    'processes' => '1',
                    'stopwaitsecs' => '10',
                    'stopsignal' => 'SIGTERM',
                ],
                [
                    'command' => 'custom-script',
                    'directory' => '/usr/local/bin',
                    'processes' => '1',
                    'stopwaitsecs' => '10',
                    'stopsignal' => 'SIGTERM',
                ],
            ],
        ],
        [
            [
                "'script-with-parameters -e --foo=bar' --directory=/path/to/daemon --user=root --processes=2 --startsecs=10 --stopwaitsecs=30 --stopsignal=SIGKILL",
            ],
            [
                [
                    'command' => 'script-with-parameters -e --foo=bar',
                    'directory' => '/path/to/daemon',
                    'user' => 'root',
                    'processes' => '2',
                    'startsecs' => '10',
                    'stopwaitsecs' => '30',
                    'stopsignal' => 'SIGKILL',
                ],
            ],
        ],
    ]);

it('shows failure message if command is not specified', function () {
    renderUsing($output = new BufferedOutput());

    ParseDaemonCommands::run(['--user=anthony']);

    expect($output->fetch())
        ->toContain('FAIL')
        ->toContain('No command was specified for daemon creation: --user=anthony');

    renderUsing(null);
});
