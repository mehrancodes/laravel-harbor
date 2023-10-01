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

namespace App\Commands;

use App\Services\Forge\ForgeService;
use App\Services\Forge\Pipeline\CreateDatabase;
use App\Services\Forge\Pipeline\EnableQuickDeploy;
use App\Services\Forge\Pipeline\FindServer;
use App\Services\Forge\Pipeline\FindSite;
use App\Services\Forge\Pipeline\InstallGitRepository;
use App\Services\Forge\Pipeline\NginxTemplateSearchReplace;
use App\Services\Forge\Pipeline\ObtainLetsEncryptCertification;
use App\Services\Forge\Pipeline\OrCreateNewSite;
use App\Traits\Outputifier;
use Illuminate\Support\Facades\Pipeline;
use LaravelZero\Framework\Commands\Command;

class ShipCommand extends Command
{
    use Outputifier;

    protected $signature = 'ship';

    protected $description = 'Prepares the preview environment for your project.';

    public function handle(ForgeService $service): void
    {
        Pipeline::send($service)
            ->through([
                FindServer::class,
                FindSite::class,
                OrCreateNewSite::class,
                NginxTemplateSearchReplace::class,
                CreateDatabase::class,
                InstallGitRepository::class,
                ObtainLetsEncryptCertification::class,
                EnableQuickDeploy::class,
            ])
            ->then(fn () => $this->success('it is done.'));
    }
}
