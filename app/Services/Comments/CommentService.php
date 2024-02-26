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

use Illuminate\Support\Collection;

class CommentService
{
    private Collection $collection;

    public function __construct()
    {
        $this->collection = new Collection();
    }

    public function setDatabase(string $database, string $username, string $password, string $host): CommentService
    {
        $commenter = new DatabaseBuilder($database, $username, $password, $host);

        $this->collection->push($this->getOutputArray($commenter));

        return $this;
    }

    public function setEnvironmentUrl(string $url): CommentService
    {
        $commenter = new EnvironmentUrlBuilder($url);

        $this->collection->push(
            $this->getOutputArray($commenter)
        );

        return $this;
    }

    public function toArray(): array
    {
        return $this->collection->toArray();
    }

    public function toMarkdown(): string
    {
        return (new MarkdownBuilder())->prepareBody($this->toArray());
    }

    private function getOutputArray(CommentInterface $commenter): array
    {
        return [
            'name' => $commenter->getName(),
            'type' => $commenter->getType(),
            'content' => $commenter->getContent(),
        ];
    }
}
