<?php

namespace App\Traits;

use function Termwind\{render};

trait Outputifier
{
    protected function information(string $message): int
    {
        render(sprintf(<<<'html'
            <div class="font-bold">
                <span class="bg-blue-400 px-2 text-white mr-1">
                    INFO
                </span>
                %s
            </div>
        html, trim($message)));

        return 0;
    }

    protected function error(string $message): int
    {
        render(sprintf(<<<'html'
            <div class="font-bold">
                <span class="bg-red-400 px-2 text-white mr-1">
                    FAIL
                </span>
                %s
            </div>
        html, trim($message)));

        return 0;
    }

    protected function warning(string $message): int
    {
        render(sprintf(<<<'html'
            <div class="font-bold">
                <span class="bg-orange-400 px-2 text-white mr-1">
                    WARNING
                </span>
                %s
            </div>
        html, trim($message)));

        return 0;
    }

    protected function success(string $message): int
    {
        render(sprintf(<<<'html'
            <div class="font-bold">
                <span class="bg-green-400 px-2 text-white mr-1">
                    SUCCESS
                </span>
                %s
            </div>
        html, trim($message)));

        return 0;
    }
}
