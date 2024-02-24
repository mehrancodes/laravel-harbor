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

use App\Traits\Outputifier;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Laravel\Forge\Exceptions\ValidationException;

class Handler extends ExceptionHandler
{
    use Outputifier;

    protected $dontReport = [];

    public function register(): void
    {
        $this->reportable(function (ValidationException $e) {
            foreach ($e->errors() as $error) {
                $this->fail(
                    sprintf('---> %s', is_array($error) ? current($error) : $error)
                );
            }

            return false;
        });
    }
}
