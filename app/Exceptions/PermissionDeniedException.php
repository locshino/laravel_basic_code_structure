<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Exception;

/**
 * Exception thrown when a user does not have sufficient permissions to perform an action.
 */
class PermissionDeniedException extends Exception
{
    protected $message = 'You do not have permission to perform this action.';
    protected $code = Response::HTTP_FORBIDDEN; // 403 Forbidden

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
         if ($message === "") $message = $this->message;
         if ($code === 0) $code = $this->code;
         parent::__construct($message, $code, $previous);
    }
}
