<?php


use LajosBencz\StreamParseSql\Tokenizer;
use PHPUnit\Framework\TestCase;

class TokenizerTest extends TestCase
{
    public function testTokenizer()
    {
        $input = "-- comment!\r\nINSERT INTO tbl (foo, bar) /* comment\r\n aswell */ VALUES ('val'); UPDATE tbl SET foo='ba\'r' WHERE bar='bar';";
        $t = new Tokenizer\SqlTokenizer;
        while(($result = $t->tokenize($input)) !== false) {
            //echo $result, PHP_EOL;
        }
        $this->assertEquals(26, count($t->tokens));
        $this->assertEquals(0, strlen($t->last_error));
    }
}
