<?php
declare(strict_types=1);

namespace LajosBencz\StreamParseSql;


use Generator;

class StreamParseSql
{
    const DEFAULT_DELIMITER = ';';

    const DEFAULT_CHUNK_SIZE = 4096;

    /** @var callable[] */
    private $_onProgress = [];

    /** @var string */
    protected $_filePath = '';

    /** @var string */
    protected $_delimiter = self::DEFAULT_DELIMITER;

    /** @var int */
    protected $_chunkSize = self::DEFAULT_CHUNK_SIZE;

    /**
     * Parses commands from given file command-by-command
     * @param string $filePath Path to large file with SQL commands
     * @param string $delimiter Command delimiter used in the SQL file
     * @param int $chunkSize Size of the read chunk
     * @throws Exception\FileReadException
     */
    public function __construct(string $filePath, string $delimiter = self::DEFAULT_DELIMITER, int $chunkSize = self::DEFAULT_CHUNK_SIZE)
    {
        $this->setDelimiter($delimiter);
        if (!is_readable($filePath)) {
            throw new Exception\FileReadException($filePath);
        }
        $this->_filePath = realpath($filePath);
        $this->_chunkSize = $chunkSize;
    }

    /**
     * Path to the large SQL file
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->_filePath;
    }

    /**
     * Sets the command delimiter used in the SQL file
     * @param string $delimiter
     */
    public function setDelimiter(string $delimiter = self::DEFAULT_DELIMITER): void
    {
        $this->_delimiter = $delimiter;
    }

    /**
     * Returns the command delimiter used in the SQL file
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->_delimiter;
    }

    /**
     * Will yield a single SQL command at a time until the file is read to the end
     * @return string[]|Generator
     * @throws Exception\FileLockException
     * @throws Exception\FileReadException
     * @throws Exception\ParseException
     */
    public function parse(): Generator
    {
        $tokenizer = new Tokenizer($this->getDelimiter());
        $tokens = [];
        $filePath = $this->getFilePath();
        $size = filesize($filePath);
        $fh = fopen($filePath, 'r');
        if (!is_resource($fh)) {
            throw new Exception\FileReadException($filePath);
        }
        if (!flock($fh, LOCK_SH)) {
            throw new Exception\FileLockException($filePath);
        }
        $pos = 0;
        while (!feof($fh)) {
            $line = fread($fh, $this->_chunkSize);
            $pos += min(strlen($line), $this->_chunkSize);
            foreach($tokenizer->append($line, feof($fh)) as $token) {
                switch($token->type) {
                    case 'COMMENT_SINGLE':
                    case 'COMMENT_MULTI':
                    case 'NEWLINE':
                        break;
                    default:
                        $tokens[] = $token;
                        if($token->type == 'DELIMITER') {
                            yield Token::Assemble(...$tokens);
                            $tokens = [];
                        }
                        break;
                }
            }
            foreach($this->_onProgress as $callback) {
                $callback($pos, $size);
            }
        }
        flock($fh, LOCK_UN);
        fclose($fh);
        if(count($tokens) > 0) {
            yield Token::Assemble(...$tokens);
        }
    }

    public function onProgress(callable $callback): void
    {
        $this->_onProgress[] = $callback;
    }
}
