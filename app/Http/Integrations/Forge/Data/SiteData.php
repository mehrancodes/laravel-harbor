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

namespace App\Http\Integrations\Forge\Data;

class SiteData
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }

    public static function fromResponse(array $data): SiteData
    {
        return new static($data['id'], $data['name']);
    }
}
