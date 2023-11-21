<?php

declare(strict_types=1);

/**
 * This file is part of Veyoze CLI.
 *
 * (c) Mehran Rasulian <mehran.rasulian@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace App\Commands;

use App\Services\Forge\ForgeService;
use App\Services\Forge\Pipeline\CreateDatabase;
use App\Services\Forge\Pipeline\DeploySite;
use App\Services\Forge\Pipeline\EnableQuickDeploy;
use App\Services\Forge\Pipeline\EnsureJobScheduled;
use App\Services\Forge\Pipeline\FindServer;
use App\Services\Forge\Pipeline\FindSite;
use App\Services\Forge\Pipeline\InstallGitRepository;
use App\Services\Forge\Pipeline\NginxTemplateSearchReplace;
use App\Services\Forge\Pipeline\ObtainLetsEncryptCertification;
use App\Services\Forge\Pipeline\OrCreateNewSite;
use App\Services\Forge\Pipeline\PutCommentOnPullRequest;
use App\Services\Forge\Pipeline\RunOptionalCommands;
use App\Services\Forge\Pipeline\UpdateDeployScript;
use App\Services\Forge\Pipeline\UpdateEnvironmentVariables;
use App\Traits\Outputifier;
use Illuminate\Support\Facades\Pipeline;
use LaravelZero\Framework\Commands\Command;

class ProvisionCommand extends Command
{
    use Outputifier;

    protected $signature = 'provision';

    protected $description = 'Deploys the preview environment on a new site.';

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
                UpdateEnvironmentVariables::class,
                UpdateDeployScript::class,
                DeploySite::class,
                RunOptionalCommands::class,
                EnsureJobScheduled::class,
                PutCommentOnPullRequest::class,
            ])
            ->then(function () use ($service) {
                $this->success('Provisioning complete! Your environment is now set up and ready to use.');
            });
    }
}
