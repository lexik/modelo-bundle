<?php

namespace Choosit\ModeloBundle\Exception;

use Exception;

class HttpException extends Exception
{
    public function __construct()
    {
        $message = 'A technical error occurred while executing request.';

        parent::__construct($message);
    }
}
