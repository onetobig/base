<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    public function __construct($msg = '', $code = 400)
    {
        parent::__construct($msg, $code);
    }


}
