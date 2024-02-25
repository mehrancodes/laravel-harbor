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

use App\Actions\SearchReplaceNginxTemplateVariables;
use App\Actions\TextToArray;
use App\Services\Forge\ForgeService;
use App\Traits\Outputifier;
use Closure;

class NginxTemplateSearchReplace
{
    use Outputifier;

    public function __invoke(ForgeService $service, Closure $next)
    {
        $nginxVariables = $service->setting->nginxVariables;

        if (empty($nginxVariables) || ! $service->siteNewlyMade) {
            return $next($service);
        }

        $service->updateSiteNginxTemplate(
            SearchReplaceNginxTemplateVariables::run(
                TextToArray::run($nginxVariables),
                $service->siteNginxTemplate()
            )
        );

        return $next($service);
    }
}
