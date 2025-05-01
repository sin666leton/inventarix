<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(string $message = "Insufficient Stock.", int $code = 422, \Throwable $previous = null)
    {
        parent::__construct($message, $code);
    }
}
