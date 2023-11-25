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

class GenerateStandardizedBranchName
{
    use AsAction;

    public function handle(string $branch): string
    {
        $firstDigitsOfStringRemoved = preg_replace(
            '/^\d+-?/',
            '',
            $branch
        );

        return Str::replace('-', '_', $firstDigitsOfStringRemoved);
    }
}
