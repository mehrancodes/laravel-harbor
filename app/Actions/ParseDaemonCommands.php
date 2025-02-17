<?php

namespace App\Actions;

use App\Services\Syntax\CommandParser;
use App\Traits\CommandSyntax;
use App\Traits\CommandSyntaxParser;
use App\Traits\Outputifier;
use Lorisleiva\Actions\Concerns\AsAction;

class ParseDaemonCommands
{
    use AsAction,
        Outputifier,
        CommandSyntax;

    protected string $signature = '{command* : The command you want to run}
                                   {--directory= : Working directory}
                                   {--user= : User to run the command under}
                                   {--processes=1 : How many processes to run}
                                   {--startsecs= : The total number of seconds the program must stay running in order to consider the start successful}
                                   {--stopwaitsecs=10 : The number of seconds Supervisor will allow for the daemon to gracefully stop before forced termination}
                                   {--stopsignal=SIGTERM : The signal used to kill the program when a stop is requested}';

    public function handle(array $commands): array
    {
        $definition = $this->definition($this->signature);

        return array_map(function (string $command) use ($definition) {
            $input = $this->input($command, $definition);

            if (blank($input->getArgument('command'))) {
                $this->failCommand("No command was specified for daemon creation: {$command}");

                return [];
            }

            return array_filter([
                'command' => implode(' ', $input->getArgument('command')),
                ...$input->getOptions(),
            ], fn ($value) => ! is_null($value));
        }, $commands);
    }
}
