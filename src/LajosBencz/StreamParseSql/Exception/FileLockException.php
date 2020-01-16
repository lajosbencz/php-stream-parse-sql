<?php


namespace LajosBencz\StreamParseSql\Exception;


use LajosBencz\StreamParseSql\Exception;
use Throwable;

class FileLockException extends Exception
{
    public function __construct(string $filePath, int $code=0, Throwable $previous=null)
    {
        parent::__construct('failed to lock file for reading: '.$filePath, $code, $previous);
    }
}