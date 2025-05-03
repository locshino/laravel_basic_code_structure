<?php
// app/Exceptions/InvalidInputException.php
namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Exception;

/**
 * Exception thrown for invalid input data at the business logic level (beyond basic validation).
 */
class InvalidInputException extends Exception
{
     protected $message = 'Invalid input data.';
     protected $code = Response::HTTP_UNPROCESSABLE_ENTITY; // 422 Unprocessable Entity

     public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
     {
          if ($message === "") $message = $this->message;
          if ($code === 0) $code = $this->code;
          parent::__construct($message, $code, $previous);
     }
}
