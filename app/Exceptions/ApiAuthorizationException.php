<?php

namespace App\Exceptions;

use App\Api\Helpers\ApiResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiAuthorizationException extends HttpException
{
    use ApiResponse;

    public function __construct(string $challenge, string $message = null, \Exception $previous = null, ?int $code = 401, array $headers = array())
    {
        $headers['WWW-Authenticate'] = $challenge;

        parent::__construct(401, $message, $previous, $headers, $code);
    }

    public function render()
    {
        return $this->failed($this->message, $this->code);
    }
}
