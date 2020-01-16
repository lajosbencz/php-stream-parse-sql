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
        $this->reset();
        $this->_tokenizer = new SqlTokenizer;
    }

    public function __toString()
    {
        return $this->_buffer;
    }

    public function reset(): void
    {
        $this->_buffer = '';
    }

    /**
     * @param string $line
     * @return string[]|Generator
     */
    public function append(string $line): Generator
    {
        $this->_tokenizer->reset();
        while($this->_tokenizer->tokenize($line) !== false) {}
        foreach($this->_tokenizer->tokens as $token) {
            if(in_array($token['name'], ['COMMENT_SINGLE', 'COMMENT_MULTI'])) {
                continue;
            }
            if($token['name'] === 'DELIMITER') {
                $ret = trim($this->_buffer);
                $this->reset();
                yield $ret;
            } else {
                $this->_buffer.= $token['token'].' ';
            }
        }
        $ret = trim($this->_buffer);
        $this->reset();
        if(strlen($ret) > 0) {
            yield $ret;
        }
    }

}