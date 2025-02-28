<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class LineBreaksToArray
{
    use AsAction;

    public function handle(?string $content): ?array
    {
        return str($content)
            ->explode("\n")
            ->map(Str::squish(...))
            ->filter()
            ->values()
            ->all();
    }
}
