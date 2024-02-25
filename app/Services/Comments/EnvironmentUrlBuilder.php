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
    public string $name = 'Environment Url';

    public string $type = 'link';

    public string $content;

    public function __construct(public string $url, public bool $isSecured)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getContent(): string
    {
        return ($this->isSecured ? 'https://' : 'http://').$this->name;
    }
}
