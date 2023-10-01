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

class ArrayToText
{
    use AsAction;

    public function handle(array $content): string
    {
        return implode("\n", array_map(function ($k, $v) {
            return "$k=$v";
        }, array_keys($content), $content))."\n";
    }
}
