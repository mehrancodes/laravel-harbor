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
use Throwable;

class ObtainLetsEncryptCertification
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (! $service->setting->sslRequired || ! $service->siteNewlyMade) {
            return $next($service);
        }

        $this->information('Processing SSL certificate operations.');

        try {
            $service->forge->obtainLetsEncryptCertificate(
                $service->server->id,
                $service->site->id,
                ['domains' => [$service->site->name]],
                $service->setting->waitOnSsl
            );
        } catch (Throwable $e) {
            $this->fail("---> Something's wrong with SSL certification. Check your Forge site Log for more info.");
        }

        return $next($service);
    }
}
