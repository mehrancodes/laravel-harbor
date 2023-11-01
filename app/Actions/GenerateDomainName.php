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

class GenerateDomainName
{
    use AsAction;

    public function handle(string $domain, string $branch, ?string $pattern): string
    {
        return str($this->formatBranchName($branch, $pattern))
            ->append('.', $domain)
            ->toString();
    }

    private function formatBranchName(string $branch, ?string $pattern): string
    {
        if (isset($pattern)) {
            preg_match($pattern, $branch, $matches);
            $branch = array_pop($matches);
        }

        return Str::slug($branch, '-', 'en', ['+' => '-', '_' => '-', '@' => '-']);
    }
}
