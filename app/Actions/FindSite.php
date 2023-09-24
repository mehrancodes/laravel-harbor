<?php

namespace App\Actions;

use App\Traits\Outputifier;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Site;
use Lorisleiva\Actions\Concerns\AsAction;

class FindSite
{
    use AsAction;
    use Outputifier;

    public function handle(Forge $forge, int $serverId, string $domain): ?Site
    {
        foreach ($forge->sites($serverId) as $site) {
            if ($site->name === $domain) {
                $this->success('Available site found.');

                return $site;
            }
        }

        return null;
    }
}
