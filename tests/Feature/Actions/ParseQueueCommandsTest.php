<?php

use App\Actions\ParseQueueCommands;
use Symfony\Component\Console\Output\BufferedOutput;

use function Termwind\renderUsing;

it('extracts api parameters from command syntax', function ($actual, $expected) {
    expect(
        ParseQueueCommands::run($actual)
    )
        ->toBe($expected);
})
    ->with([
        [
            [
                'redis',
                'database --queue=default --daemon',
                'sqs --force --delay=0',
            ],
            [
                [
                    'connection' => 'redis',
                    'sleep' => '3',
                    'php_version' => 'php',
                    'daemon' => false,
                    'force' => false,
                ],
                [
                    'connection' => 'database',
                    'queue' => 'default',
                    'sleep' => '3',
                    'php_version' => 'php',
                    'daemon' => true,
                    'force' => false,
                ],
                [
                    'connection' => 'sqs',
                    'sleep' => '3',
                    'delay' => '0',
                    'php_version' => 'php',
                    'daemon' => false,
                    'force' => true,
                ],
            ],
        ],
        [
            [
                'redis --queue=high,default --timeout=60 --sleep=10 --delay=2 --tries=3 --php_version=php84 --environment=preview --memory=1024 --directory=/path/to/app --numprocs=8 --stopwaitsecs=60 --daemon --force',
            ],
            [
                [
                    'connection' => 'redis',
                    'queue' => 'high,default',
                    'timeout' => '60',
                    'sleep' => '10',
                    'delay' => '2',
                    'tries' => '3',
                    'php_version' => 'php84',
                    'environment' => 'preview',
                    'memory' => '1024',
                    'directory' => '/path/to/app',
                    'numprocs' => '8',
                    'stopwaitsecs' => '60',
                    'daemon' => true,
                    'force' => true,
                ],
            ],
        ],
    ]);

it('shows failure message if connection is not specified', function () {
    renderUsing($output = new BufferedOutput());

    ParseQueueCommands::run(['--queue=default']);

    expect($output->fetch())
        ->toContain('FAIL')
        ->toContain('No queue connection was specified for command: --queue=default');

    renderUsing(null);
});
