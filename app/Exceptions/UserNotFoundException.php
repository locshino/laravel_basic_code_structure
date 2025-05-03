<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Exception;

/**
 * Exception thrown when a user is not found in the system.
 */
class UserNotFoundException extends Exception
{
    protected $message = 'User not found.';
    protected $code = Response::HTTP_NOT_FOUND; // 404 Not Found

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
         if ($message === "") $message = $this->message;
         if ($code === 0) $code = $this->code;
         parent::__construct($message, $code, $previous);
    }
}
