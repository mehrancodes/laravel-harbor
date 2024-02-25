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

class DatabaseBuilder implements CommentInterface
{
    public function __construct(public string $database, public string $username, public string $password, public string $host)
    {
    }

    public function getName(): string
    {
        return 'DatabaseBuilder Url';
    }

    public function getType(): string
    {
        return 'text';
    }

    public function getContent(): string
    {
        return sprintf(
            'mysql+ssh://forge@%s/%s:%s@127.0.0.1/%s',
            $this->host,
            $this->username,
            $this->password,
            $this->database,
        );
    }
}
