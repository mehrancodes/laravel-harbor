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

namespace App\Exceptions;

use App\Services\Forge\Api\Exceptions\ForgeApiException;
use App\Services\Forge\Exceptions\ValidationException as ForgeSettingValidationException;
use App\Traits\Outputifier;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    use Outputifier;

    protected $dontReport = [];

    public function register(): void
    {
        $render = function (array $errors): bool {
            foreach ($errors as $error) {
                $this->failCommand(
                    sprintf('---> %s', is_array($error) ? current($error) : $error)
                );
            }

            return false;
        };

        $this->reportable(function (ForgeSettingValidationException $e) use ($render) {
            return $render($e->errors());
        });

        $this->reportable(function (ForgeApiException $e) use ($render) {
            return $render($e->errors() !== [] ? $e->errors() : [$e->getMessage()]);
        });

        $this->reportable(function (\Illuminate\Validation\ValidationException $e) use ($render) {
            $errors = collect($e->errors())->flatten()->values()->all();

            return $render($errors);
        });
    }
}
