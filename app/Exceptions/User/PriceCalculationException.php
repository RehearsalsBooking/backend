<?php

namespace App\Exceptions\User;

use Exception;
use Throwable;

class PriceCalculationException extends Exception
{
    public function __construct(
        string $message = 'Не получилось рассчитать цену, попробуйте выбрать другое время или обратитесь к администратору',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
