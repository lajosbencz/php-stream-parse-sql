<?php

namespace LajosBencz\StreamParseSql\Exception;


use Throwable;
use LajosBencz\StreamParseSql\Exception;

class ParseException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('failed to parse: [' . $message . ']', $code, $previous);
    }
}