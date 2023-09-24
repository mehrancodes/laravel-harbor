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
use Illuminate\Support\Facades\Validator;
use LaravelZero\Framework\Commands\Command;
use Throwable;

class DeployCommand extends Command
{
    protected $signature = 'deploy';

    protected $description = 'Deploy Command';

    public function handle(): int
    {
        $config = config('services.forge');

        if (! $this->passesPreflightValidation($config)) {
            $this->error('The required forge configuration is missing.');

            return 2;
        }

        $connector = new ForgeConnector($config['token']);

        try {
            $server = $connector->server()
                ->firstById($config['server'])
                ->dtoOrFail();
        } catch (Throwable $throwable) {
            $this->error('Server not found.');

            return 2;
        }

        $site = $connector->site()
            ->firstOrCreate($server->id);

        return 0;
    }

    protected function passesPreflightValidation(array $config): bool
    {
        $validator = Validator::make(
            $config,
            [
                'token' => ['required'],
                'server' => ['required'],
                'domain' => ['required'],
                'git' => [
                    'repository' => ['required'],
                    'branch' => ['required'],
                ],
            ]
        );

        return $validator->passes();
    }
}
