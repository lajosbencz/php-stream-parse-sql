<?php


namespace LajosBencz\StreamParseSql;


use Generator;
use LajosBencz\StreamParseSql\Tokenizer\SqlTokenizer;

class Buffer
{
    protected $_buffer = '';

    protected $_tokenizer;

    public function __construct()
    {
        $this->clear();
        $this->_tokenizer = new SqlTokenizer;
    }

    public function __toString()
    {
        return $this->_buffer;
    }

    public function clear(): void
    {
        $this->_buffer = '';
    }

    /**
     * @param string $line
     * @return string[]|Generator
     * @throws \Throwable
     */
    public function append(string $line): Generator
    {
        $this->_tokenizer->reset();
        while ($this->_tokenizer->tokenize($line) !== null) { }
        foreach ($this->_tokenizer->getTokens() as $token) {
            if (in_array($token['name'], ['COMMENT_SINGLE', 'COMMENT_MULTI'])) {
                continue;
            }
            switch($token['name']) {
                case 'NEWLINE':
                    $this->_buffer.= ' ';
                    break;
                case 'DELIMITER':
                    $ret = trim($this->_buffer);
                    $this->clear();
                    yield $ret;
                    break;
                default:
                    $this->_buffer .= $token['token'];
                    break;
            }
        }
        $ret = trim($this->_buffer);
        $this->clear();
        if (strlen($ret) > 0) {
            yield $ret;
        }
    }

}