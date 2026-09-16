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

namespace App\Services\Git;

use App\Services\Git\Contracts\GitProvider;
use App\Traits\Outputifier;

/**
 * Used when Harbor cannot reach a Git API, for instance a self-hosted provider
 * without a token. Harbor still provisions the site and reports what is left
 * to do by hand instead of failing the workflow.
 */
class ManualProvider implements GitProvider
{
    use Outputifier;

    public function name(): string
    {
        return 'your Git provider';
    }

    public function createDeployKey(string $title, string $key, bool $readOnly = true): void
    {
        $this->warning('---> Harbor has no Git API configured, so the deploy key was not registered automatically.');
        $this->information(sprintf('---> Add this key to your repository as "%s":', $title));
        $this->information($key);
    }

    public function deleteDeployKeysByTitle(string $title): void
    {
        $this->warning(sprintf('---> Harbor has no Git API configured. Remove the deploy key named "%s" manually.', $title));
    }

    public function comment(string $issueNumber, string $body): void
    {
        $this->warning('---> Harbor has no Git API configured, so no comment was posted. Set FORGE_GIT_API_PROVIDER and GIT_TOKEN to enable comments.');
    }
}
