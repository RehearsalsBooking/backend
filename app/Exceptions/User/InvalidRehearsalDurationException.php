<?php

namespace App\Exceptions\User;

use Exception;
use Throwable;

class InvalidRehearsalDurationException extends Exception
{
    public function __construct(string $message = 'Некорректная длительность репетиции', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
