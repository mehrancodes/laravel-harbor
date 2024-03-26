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

namespace App\Services\Comments;

class EnvironmentUrlBuilder implements CommentInterface
{
    public function __construct(public string $url)
    {
    }

    public function getName(): string
    {
        return 'Environment Url';
    }

    public function getType(): string
    {
        return 'link';
    }

    public function getContent(): string
    {
        return $this->url;
    }
}
