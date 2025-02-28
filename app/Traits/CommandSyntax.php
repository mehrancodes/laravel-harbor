<?php

namespace App\Traits;

use Illuminate\Console\Parser;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\StringInput;

trait CommandSyntax
{
    protected function input(string $command, InputDefinition $definition): StringInput
    {
        $input = new StringInput($command);
        $input->bind($definition);

        return $input;
    }

    protected function definition(string $signature): InputDefinition
    {
        [, $arguments, $options] = Parser::parse($signature);

        $definition = new InputDefinition();
        $definition->setArguments($arguments);
        $definition->setOptions($options);

        return $definition;
    }
}
