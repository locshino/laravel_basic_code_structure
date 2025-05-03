<?php

namespace App\Exceptions;

use Exception;

/**
 * A base exception for all custom business logic errors.
 * Useful for catching all custom exceptions with one catch block if needed.
 */
class BusinessLogicException extends Exception
{
    protected $message = 'A business logic error occurred.';
    protected $code = 500; // Internal Server Error

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        if ($message === "") $message = $this->message;
        if ($code === 0) $code = $this->code;
        parent::__construct($message, $code, $previous);
    }
}
