<?php

declare(strict_types=1);

/**
 * This file is part of Laravel Harbor.
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

        $output = [];
        foreach (explode($separator, $content) as $variable) {
            if (empty($variable)) {
                continue;
            }

            $var = explode('=', $variable, 2);

            if (empty($var[0]) || empty($var[1])) {
                continue;
            }

            $output[$var[0]] = $var[1];
        }

        return $output;
    }
}
