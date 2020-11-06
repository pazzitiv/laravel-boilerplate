<?php


namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class AlreadyExistsException extends HttpException
{
    public function __construct(string $message = 'alreadyExist', int $statusCode = 409, \Throwable $previous = null, array $headers = [], ?int $code = 0)
    {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }
}
