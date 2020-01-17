<?php

namespace LajosBencz\StreamParseSql\Exception;


use LajosBencz\StreamParseSql\Exception;
use Throwable;

class ParseException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('failed to parse: [' . $message . ']', $code, $previous);
    }
}