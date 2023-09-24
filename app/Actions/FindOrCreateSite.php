<?php

namespace App\Actions;

use App\Traits\Outputifier;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Site;
use Lorisleiva\Actions\Concerns\AsAction;

class FindOrCreateSite
{
    use AsAction;
    use Outputifier;

    public function handle(Forge $forge, int $serverId): Site
    {
        $domain = GenerateDomain::run();

        if ($site = FindSite::run($forge, $serverId, $domain)) {
            return $site;
        }

        return SetupNewSite::run($forge, $serverId, $domain);
    }
}
