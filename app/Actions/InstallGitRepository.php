<?php

namespace App\Actions;

use App\Traits\Outputifier;
use Laravel\Forge\Resources\Site;
use Lorisleiva\Actions\Concerns\AsAction;

class InstallGitRepository
{
    use AsAction;
    use Outputifier;

    public function handle(Site $site): Site
    {
        $this->information('Installing the git repository...');

        $data = [
            'provider' => config('services.forge.git.provider'),
            'repository' => config('services.forge.git.repository'),
            'branch' => config('services.forge.git.branch'),
            'composer' => false,
        ];

        return $site->installGitRepository($data);
    }
}
