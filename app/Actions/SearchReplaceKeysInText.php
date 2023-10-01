<?php

declare(strict_types=1);

/**
 * This file is part of Harbor CLI.
 *
 * (c) Mehran Rasulian <mehran.rasulian@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;

class SearchReplaceKeysInText
{
    use AsAction;

    public function handle(string $substitutes, string $template): string
    {
        foreach (explode(',', $substitutes) as $substitute) {
            [$key, $value] = explode(':', $substitute, 2);

            $template = str_replace($key, $value, $template);
        }

        return $template;
    }
}
