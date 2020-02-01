<?php

namespace App\Exceptions\User;

use Exception;
use Throwable;

class PriceCalculationException extends Exception
{
    public function __construct($message = 'Произошла ошибка при расчете цены', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
