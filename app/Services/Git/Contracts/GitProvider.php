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

namespace App\Services\Git\Contracts;

interface GitProvider
{
    /**
     * The human readable provider name, used for console output.
     */
    public function name(): string;

    /**
     * Register a deploy key on the repository.
     */
    public function createDeployKey(string $title, string $key, bool $readOnly = true): void;

    /**
     * Remove every deploy key on the repository matching the given title.
     */
    public function deleteDeployKeysByTitle(string $title): void;

    /**
     * Comment on the pull or merge request the preview belongs to.
     */
    public function comment(string $issueNumber, string $body): void;
}
