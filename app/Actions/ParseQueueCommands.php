<?php

namespace App\Actions;

use App\Traits\Outputifier;
use Illuminate\Console\Parser;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\StringInput;

class ParseQueueCommands
{
    use AsAction,
        Outputifier;

    private string $signature = '{connection : The name of the queue connection to work}
                                {--queue= : The names of the queues to work}
                                {--timeout= : Maximum seconds a job can run}
                                {--sleep=3 : Number of seconds to sleep when no job is available}
                                {--delay= : The number of seconds to delay failed jobs}
                                {--tries= : Number of times to attempt a job before logging it failed}
                                {--php_version=php : PHP version to use}
                                {--environment= : Environment to run the queue worker in}
                                {--memory= : The memory limit in megabytes}
                                {--directory= : Working directory}
                                {--numprocs= : Number of workers to run}
                                {--stopwaitsecs= : Graceful shutdown seconds allowed for the worker}
                                {--daemon : Run worker using queue:work instead of queue:listen}
                                {--force : Force the worker to run even in maintenance mode}';

    public function handle(array $commands): array
    {
        $definition = $this->definition();

        return array_map(function ($command) use ($definition) {
            $input = new StringInput($command);
            $input->bind($definition);

            if (blank($input->getArgument('connection'))) {
                $this->failCommand("No queue connection was specified for command: {$command}");

                return [];
            }

            return array_filter([
                ...$input->getArguments(),
                ...$input->getOptions(),
            ], fn ($value) => ! is_null($value));
        }, $commands);
    }

    public function definition(): InputDefinition
    {
        [, $arguments, $options] = Parser::parse($this->signature);

        $definition = new InputDefinition();
        $definition->setArguments($arguments);
        $definition->setOptions($options);

        return $definition;
    }
}
