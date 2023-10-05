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

namespace App\Services\Forge\Pipeline;

use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;
use Closure;

class DeploySite
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        $this->information('Start deploying the site.');

        $service->site->deploySite($service->setting->waitOnDeploy);

        return $next($service);
    }
}
