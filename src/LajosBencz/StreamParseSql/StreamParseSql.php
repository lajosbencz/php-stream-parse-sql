<?php
declare(strict_types=1);

namespace LajosBencz\StreamParseSql;


use Generator;

class StreamParseSql
{
    use DelimiterAwareTrait;

    protected $_filePath;

    public function __construct(string $filePath, string $delimiter=DEFAULT_DELIMITER)
    {
        $this->setDelimiter($delimiter);
        if(!is_readable($filePath)) {
            throw new Exception\FileReadException($filePath);
        }
        $this->_filePath = realpath($filePath);
    }

    public function getFilePath(): string
    {
        return $this->_filePath;
    }

    public function parse(): Generator
    {
        $filePath = $this->getFilePath();
        $fh = fopen($filePath, 'r');
        if(!is_resource($fh)) {
            throw new Exception\FileReadException($filePath);
        }
        if(!flock($fh, LOCK_SH)) {
            throw new Exception\FileLockException($filePath);
        }
        $buffer = new Buffer;
        while(($line = fgets($fh)) && !feof($fh)) {
            foreach($buffer->append($line) as $command) {
                if($command) {
                    yield $command;
                }
            }
        }
        flock($fh, LOCK_UN);
        fclose($fh);
    }
}
