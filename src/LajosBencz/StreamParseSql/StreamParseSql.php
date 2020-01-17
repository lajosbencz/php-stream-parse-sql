<?php
declare(strict_types=1);

namespace LajosBencz\StreamParseSql;


use Generator;

class StreamParseSql
{
    const DEFAULT_DELIMITER = ';';

    const DEFAULT_CHUNK_SIZE = 4096;

    protected $_filePath = '';

    protected $_delimiter = self::DEFAULT_DELIMITER;

    protected $_chunkSize = self::DEFAULT_CHUNK_SIZE;

    public function __construct(string $filePath, string $delimiter = self::DEFAULT_DELIMITER, int $chunkSize = self::DEFAULT_CHUNK_SIZE)
    {
        $this->setDelimiter($delimiter);
        if (!is_readable($filePath)) {
            throw new Exception\FileReadException($filePath);
        }
        $this->_filePath = realpath($filePath);
        $this->_chunkSize = $chunkSize;
    }

    public function getFilePath(): string
    {
        return $this->_filePath;
    }

    public function setDelimiter(string $delimiter = self::DEFAULT_DELIMITER): void
    {
        $this->_delimiter = $delimiter;
    }

    public function getDelimiter(): string
    {
        return $this->_delimiter;
    }

    public function parse(): Generator
    {
        $tokenizer = new Tokenizer;
        $tokens = [];
        $filePath = $this->getFilePath();
        $fh = fopen($filePath, 'r');
        if (!is_resource($fh)) {
            throw new Exception\FileReadException($filePath);
        }
        if (!flock($fh, LOCK_SH)) {
            throw new Exception\FileLockException($filePath);
        }
        while (($line = fread($fh, $this->_chunkSize)) && !feof($fh)) {
            foreach($tokenizer->append($line) as $token) {
                switch($token->pattern->name) {
                    case 'COMMENT_SINGLE':
                    case 'COMMENT_MULTI':
                        yield $token->content;
                        break;
                    default:
                        $tokens[] = $token;
                        if($token->pattern->name === 'DELIMITER') {
                            yield Token::Assemble(...$tokens);
                            $tokens = [];
                        }
                        break;
                }
            }
        }
        flock($fh, LOCK_UN);
        fclose($fh);
        if(count($tokens) > 0) {
            yield Token::Assemble(...$tokens);
        }
    }
}
