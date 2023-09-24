<?php

namespace App\Actions;

use App\Traits\Outputifier;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Certificate;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleDomainCertification
{
    use AsAction;
    use Outputifier;

    public function handle(Forge $forge, int $siteId, string $domain): ?Certificate
    {
        $this->information('Handling the SSL certification...');

        if (! config('services.forge.ssl_required')) {
            return null;
        }

        return $forge->obtainLetsEncryptCertificate(
            config('services.forge.server'),
            $siteId,
            ['domains' => [$domain]],
            config('services.forge.wait_on_ssl')
        );
    }
}
