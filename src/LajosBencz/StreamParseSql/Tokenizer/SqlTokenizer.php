<?php


namespace LajosBencz\StreamParseSql\Tokenizer;


use LajosBencz\StreamParseSql\Tokenizer;

class SqlTokenizer extends Tokenizer
{
    public function __construct($delimiter = "#")
    {
        parent::__construct($delimiter);

        $strings = <<<'SCRIPT'
/^("|')(\\?.)*?\1/
SCRIPT;

        $comment = <<<'SCRIPT'
/^\/\*(\r|\n|.)*\*\//
SCRIPT;

        $this->add("NEWLINE", '/^(\r)?\n/');
        $this->add("COMMENT_SINGLE", '/^--(.+)($|(\r)?\n)/');
        $this->add("COMMENT_MULTI", $comment);

        $this->add("BRACKET_OPEN", "/^\(/");
        $this->add("BRACKET_CLOSE", "/^\)/");

        $this->add("STRING", $strings);

        $this->add('WHITESPACE', '/^[\s]/');
        $this->add('WORD', '/^[a-zA-Z_][a-zA-Z0-9_]*/');
        $this->add('DELIMITER', '/^[;]/');
        $this->add('ANY', '/^./');
    }
}