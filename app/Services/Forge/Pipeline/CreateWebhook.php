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
use Laravel\Forge\Exceptions\ValidationException;

/**
 * Creates a webhook for the Forge site.
 *
 * This pipeline step will create a webhook on the Forge site if a webhook URL
 * is provided in the configuration. The webhook will be triggered whenever
 * the site is deployed, allowing for integration with external services.
 */
class CreateWebhook
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (! $service->setting->webhookUrl) {
            return $next($service);
        }

        $this->information('Creating webhook for the site.');

        try {
            $service->forge->createWebhook(
                $service->server->id,
                $service->site->id,
                ['url' => $service->setting->webhookUrl]
            );
        } catch (ValidationException $e) {
            $this->warning(sprintf('Failed to create webhook: %s', $e->getMessage()));
        }

        return $next($service);
    }
}
