<?php

namespace App\Traits;

use function Termwind\{render};

trait Outputifier
{
    protected function information(string $message): int
    {
        render(sprintf(<<<'html'
            <div class="font-bold">
                <span class="bg-blue px-2 text-white mr-1">
                    INFO
                </span>
                %s
            </div>
        html, trim($message)));

        return 0;
    }

    protected function fail(string $message): int
    {
        render(sprintf(<<<'html'
            <div class="font-bold">
                <span class="bg-red px-2 text-white mr-1">
                    FAIL
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
                <span class="bg-green px-2 text-white mr-1">
                    SUCCESS
                </span>
                %s
            </div>
        html, trim($message)));

        return 0;
    }
}
