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

namespace App\Services\Forge\Pipeline;

use App\Services\Forge\ForgeService;
use App\Services\Git\Contracts\GitProvider;
use App\Traits\Outputifier;
use Closure;
use RuntimeException;

/**
 * Forge's create-site endpoint requires a keypair when using deploy keys.
 * Generate one (or use BYO), register the public key on the Git provider,
 * then OrCreateNewSite can pass both halves into Forge.
 */
class PrepareDeployKey
{
    use Outputifier;

    public function __construct(public GitProvider $gitProvider) {}

    public function __invoke(ForgeService $service, Closure $next)
    {
        if (! $service->setting->deployKey || ! is_null($service->site)) {
            return $next($service);
        }

        if ($service->setting->deployKeyPublic && $service->setting->deployKeyPrivate) {
            $this->information(sprintf(
                '---> Using provided deploy key pair and registering it on %s.',
                $this->gitProvider->name()
            ));

            $this->gitProvider->createDeployKey(
                $service->getDeployKeyTitle(),
                $service->setting->deployKeyPublic
            );

            return $next($service);
        }

        $this->information(sprintf(
            '---> Generating a deploy key and registering it on %s.',
            $this->gitProvider->name()
        ));

        [$publicKey, $privateKey] = $this->generateKeyPair();

        $this->gitProvider->createDeployKey(
            $service->getDeployKeyTitle(),
            $publicKey
        );

        $service->setting->deployKeyPublic = $publicKey;
        $service->setting->deployKeyPrivate = $privateKey;

        return $next($service);
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function generateKeyPair(): array
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'harbor-deploy-key-'.uniqid('', true);

        $command = sprintf(
            'ssh-keygen -t ed25519 -f %s -N "" -C %s 2>&1',
            escapeshellarg($path),
            escapeshellarg('harbor-deploy-key')
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || ! is_file($path) || ! is_file($path.'.pub')) {
            throw new RuntimeException(
                'Failed to generate a deploy key pair: '.implode("\n", $output)
            );
        }

        $privateKey = file_get_contents($path);
        $publicKey = trim((string) file_get_contents($path.'.pub'));

        @unlink($path);
        @unlink($path.'.pub');

        if ($privateKey === false || $publicKey === '') {
            throw new RuntimeException('Failed to read the generated deploy key pair.');
        }

        return [$publicKey, $privateKey];
    }
}
