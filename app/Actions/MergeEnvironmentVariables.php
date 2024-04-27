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

use App\Traits\Outputifier;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class MergeEnvironmentVariables
{
    use AsAction;
    use Outputifier;

    public function handle(string $source, array $newVariables): string
    {
        $output = '';

        if (! empty($source)) {
            $output = $this->searchReplaceExistingVariables($source, $newVariables);
        }

        foreach ($newVariables as $newKey => $newValue) {
            $output .= "$newKey=$newValue\n";
        }

        return $output;
    }

    protected function searchReplaceExistingVariables(string $source, array &$newVariables): string
    {
        // Determine the separator based on the source string
        $separator = Str::contains($source, ';') ? ';' : "\n";
        $output = '';

        foreach (explode($separator, $source) as $variable) {
            // If the variable is empty, add a newline to the output
            if (empty($variable)) {
                $output .= "\n";
                continue;
            }

            if (Str::contains($variable, '#')) {
                $output .= "$variable\n";
                continue;
            }

            [$key, $value] = explode('=', $variable, 2);

            // If the key is empty, issue a warning and skip
            if (empty($key)) {
                $this->warning("No key found for the assigned value \"$value\" inside your environment variables! Make sure to remove it.");
                continue;
            }

            $value = array_key_exists($key, $newVariables) ? Arr::pull($newVariables, $key) : $value;

            $output .= "$key=$value\n";
        }

        return $output;
    }
}
