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

namespace App\Services\Forge\Pipeline;

use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;
use Closure;

class RunOptionalCommands
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        if ($content = $service->setting->command) {
            $this->information('Executing site command(s).');

            $service->forge->executeSiteCommand($service->setting->server, $service->site->id, [
                'command' => $content,
            ]);
        }

        return $next($service);
    }
}
