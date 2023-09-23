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

namespace App\Http\Integrations\Forge\Resources;

use App\Http\Integrations\Forge\Requests\GetServerRequest;
use Saloon\Contracts\Response;

class ServerResource extends Resource
{
    public function firstById($id): Response
    {
        return $this->connector->send(
            new GetServerRequest($id)
        );
    }
}
