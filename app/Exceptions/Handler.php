<?php

namespace App\Exceptions;

use App\Traits\Outputifier;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Laravel\Forge\Exceptions\ValidationException;

class Handler extends ExceptionHandler
{
    use Outputifier;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        // \Illuminate\Database\Eloquent\ModelNotFoundException::class,
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void
    {
        $this->reportable(function (ValidationException $e) {
            foreach ($e->errors() as $error) {

                $this->fail('--> ' . current($error));
            }

            return false;
        });
    }
}
