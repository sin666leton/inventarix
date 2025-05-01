<?php

namespace App\Exceptions;

use Exception;

class ActionForbiddenException extends Exception
{
    public function __construct(string $message = "You don't have permission for this action.", int $code = 403, \Throwable $previous = null)
    {
        parent::__construct($message, $code);
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage()
        ], $this->getCode());
    }
}
