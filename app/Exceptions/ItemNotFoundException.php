<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ItemNotFoundException extends ModelNotFoundException
{
    public function __construct(string $message = "Item Not Found.", int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code);
    }
}
