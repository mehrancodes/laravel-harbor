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

class FormattedBranchName
{
    use AsAction;

    protected const STRING_LIMIT = 64;

    protected const SLUGIFY_DICTIONARY = ['+' => '-', '_' => '-', '@' => '-'];

    public function handle(string $branch, ?string $pattern): string
    {
        if (isset($pattern)) {
            preg_match($pattern, $branch, $matches);
            $branch = array_pop($matches);
        }

        $slugged = $this->slugifyIt($branch);

        return Str::limit(
            $slugged,
            self::STRING_LIMIT
        );
    }

    protected function slugifyIt(string $branch): string
    {
        return Str::slug($branch, '-', 'en', self::SLUGIFY_DICTIONARY);
    }
}
