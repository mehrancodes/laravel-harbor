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

namespace App\Commands;

use App\Http\Integrations\Forge\ForgeConnector;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use LaravelZero\Framework\Commands\Command;
use Throwable;

class DeployCommand extends Command
{
    protected $signature = 'deploy';

    protected $description = 'Deploy Command';

    public function handle(): int
    {
        $config = Config::get('services.forge');

        $validator = Validator::make($config, [
            'token' => ['required'],
            'server' => ['required'],
        ]);

        if ($validator->fails()) {
            $this->error('The required forge configuration is missing.');

            return 2;
        }

        $connector = new ForgeConnector(
            apiToken: $config['token']
        );

        // find the server
        try {
            $server = $connector->server()
                ->first($config['server'])
                ->dtoOrFail();
        } catch (Throwable $throwable) {
            $this->error('Unable to find the server.');

            return 2;
        }

        return 0;
    }
}
