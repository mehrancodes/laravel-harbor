<?php

declare(strict_types=1);

/**
 * This file is part of Veyoze CLI.
 *
 * (c) Mehran Rasulian <mehran.rasulian@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace App\Actions;

use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class TextToArray
{
    use AsAction;

    public function handle(?string $content): ?array
    {
        if (empty($content)) {
            return [];
        }

        $separator = Str::contains($content, ';') ? ';' : "\n";

        parse_str(str_replace($separator, '&', $content), $output);

        return $output;
    }
}
