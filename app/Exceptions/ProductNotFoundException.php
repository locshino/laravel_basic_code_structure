<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response; // Import Symfony Response for HTTP status codes
use Exception; // Extend PHP's base Exception class

/**
 * Exception thrown when a product is not found in the system.
 */
class ProductNotFoundException extends Exception
{
    /**
     * Default error message for this exception.
     *
     * @var string
     */
    protected $message = 'Product not found.';

    /**
     * Default HTTP status code associated with this exception.
     *
     * @var int
     */
    protected $code = Response::HTTP_NOT_FOUND; // 404 Not Found

    /**
     * Constructor.
     * Allows overriding the default message and code.
     *
     * @param string $message The error message.
     * @param int $code The error code.
     * @param \Throwable|null $previous The previous throwable used for the exception chaining.
     */
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        // Use default message if none is provided
        if ($message === "") {
            $message = $this->message;
        }
        // Use default code if none is provided
        if ($code === 0) {
            $code = $this->code;
        }
        // Call the parent Exception constructor
        parent::__construct($message, $code, $previous);
    }
}
