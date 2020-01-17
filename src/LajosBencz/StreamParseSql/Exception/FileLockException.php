<?php


namespace LajosBencz\StreamParseSql\Exception;


use Throwable;
use LajosBencz\StreamParseSql\Exception;

class FileLockException extends Exception
{
    public function __construct(string $filePath, int $code=0, Throwable $previous=null)
    {
        parent::__construct('failed to lock file for reading: '.$filePath, $code, $previous);
    }
}