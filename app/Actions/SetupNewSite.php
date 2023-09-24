<?php

namespace App\Actions;

use App\Traits\Outputifier;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Site;
use Lorisleiva\Actions\Concerns\AsAction;

class SetupNewSite
{
    use AsAction;
    use Outputifier;

    public function handle(Forge $forge, int $serverId, string $domain): Site
    {
        /** @var Site $site */
        $site = CreateSite::run($forge, $serverId, $domain);

        InstallGitRepository::run($site);

        HandleDomainCertification::run($forge, $site->id, $domain);

        if (config('services.forge.quick_deploy')) {
            $site->enableQuickDeploy();

            $this->information('Enabled the Quick Deploy on site.');
        }

        return $site;
    }
}
