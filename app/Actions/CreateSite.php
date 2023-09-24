<?php

namespace App\Actions;

use App\Traits\Outputifier;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Site;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateSite
{
    use AsAction;
    use Outputifier;

    public function handle(Forge $forge, int $serverId, string $domain): Site
    {
        $this->information('Creating a new site...');

        $data = [
            'domain' => $domain,
            'project_type' => config('services.forge.project_type'),
            'php_version' => config('services.forge.php_version'),
            'directory' => '/public',
        ];

        if ($nginxTemplate = config('services.forge.nginx_template')) {
            $this->information('Using the predefined Nginx template.');

            $data['nginx_template'] = $nginxTemplate;
        }

        if (config('services.forge.site_isolation')) {
            $this->information("It's time to isolate the site.");

            $data['isolation'] = true;
            $data['username'] = str(config('services.forge.git.branch'))->slug();
        }

        if ($site = $forge->createSite($serverId, $data)) {
            $this->information('Your site is created! But wait, some steps left yet.');
        }

        return $site;
    }
}
