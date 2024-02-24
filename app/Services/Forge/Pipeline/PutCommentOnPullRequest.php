<?php

declare(strict_types=1);

/**
 * This file is part of Veyoze CLI.
 *
 * (c) Shvan Sheikha <shvansheikha@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace App\Services\Forge\Pipeline;

use App\Services\Comments\CommentService;
use App\Services\Forge\ForgeService;
use App\Services\Github\GithubService;
use App\Traits\Outputifier;
use Closure;

class PutCommentOnPullRequest
{
    use Outputifier;

    public function __construct(public GithubService $githubService, public CommentService $commentService)
    {
    }

    public function __invoke(ForgeService $service, Closure $next)
    {
        if ($service->setting->gitCommentEnabled && $service->siteNewlyMade) {

            $this->information('Including the site information to the pull request.');

            $this->githubService->putCommentOnGithubPullRequest($this->getTable($service));
        }

        return $next($service);
    }

    protected function getTable(ForgeService $service): string
    {

        $this->commentService->setEnvironmentUrl(
            ($service->site->isSecured ? 'https://' : 'http://').$service->site->name
        );

        if ($service->setting->dbCreationRequired) {
            $this->commentService->setDatabase(
                $service->database['DB_DATABASE'],
                $service->database['DB_USERNAME'],
                $service->database['DB_PASSWORD'],
                $service->server->ipAddress,
            );
        }

        return $this->commentService->toMarkdown();
    }
}
