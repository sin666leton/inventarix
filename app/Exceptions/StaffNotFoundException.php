<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StaffNotFoundException extends ModelNotFoundException
{
    public function __construct(string $message = "User Not Found.", int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code);
    }
}
