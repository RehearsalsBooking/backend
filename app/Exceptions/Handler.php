<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Sentry\State\HubInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $exception)
    {
        if ($this->shouldReport($exception)) {
            app(HubInterface::class)->captureException($exception);
        }

        parent::report($exception);
    }
}
