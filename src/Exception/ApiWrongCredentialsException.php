<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiWrongCredentialsException extends HttpException
{
    public function __construct(int $statusCode = 401, \Throwable $previous = null, array $headers = [], int $code = 0)
    {
        parent::__construct($statusCode, 'Wrong credentials', $previous, $headers, $code);
    }
}
