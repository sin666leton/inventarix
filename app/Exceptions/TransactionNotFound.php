<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class TransactionNotFound extends ModelNotFoundException
{
    public function __construct(string $message = "Transaction Not Found.", int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code);
    }
}
