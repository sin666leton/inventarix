<?php

namespace App\Exceptions;

use Exception;

class BadRequestException extends Exception
{
    public function __construct(string $message = "", int $code = 400, \Throwable $previous = null)
    {
        parent::__construct($message, $code);
    }
}
