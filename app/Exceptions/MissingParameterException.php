<?php

namespace App\Exceptions;

use Exception;

class MissingParameterException extends Exception
{
    public $param;

    public function __construct(string $params, string $message = "Missing required parameter: ", int $code = 422, \Throwable $previous = null)
    {
        parent::__construct($message.$params, $code);
        $this->param = $params;
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage()."".$this->param
        ], $this->getCode());
    }
}
