<?php

namespace App\Exceptions;

use Exception;

class InvalidCredentialException extends Exception
{
    public function __construct(string $message = "Wrong email or password.", int $code = 401, \Throwable $previous = null)
    {
        parent::__construct($message, $code);
    }
}
