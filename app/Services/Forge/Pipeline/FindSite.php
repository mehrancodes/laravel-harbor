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

namespace App\Services\Forge\Pipeline;

use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;
use Closure;

class FindSite
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        $this->information('Finding the associated site.');

        $site = $service->findSite($service->setting->server);

        isset($site) ? $service->setSite($site) : $this->information('---> Site not found.');

        return $next($service);
    }
}
