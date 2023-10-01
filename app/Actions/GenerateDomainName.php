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

use App\Services\Forge\ForgeService;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateDomainName
{
    use AsAction;

    public function __construct(public ForgeService $service)
    {
    }

    public function handle(): string
    {
        return str($this->formatBranchName())
            ->append('.', $this->service->setting->domain)
            ->toString();
    }

    private function formatBranchName(): string
    {
        $branch = $this->service->setting->branch;
        $pattern = $this->service->setting->subdomainPattern;

        if (isset($pattern)) {
            preg_match($pattern, $branch, $new);

            return Str::slug(current($new));
        }

        return $branch;
    }
}
