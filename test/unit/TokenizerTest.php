<?php


use LajosBencz\StreamParseSql\Tokenizer;
use LajosBencz\StreamParseSql\TokenPattern;
use PHPUnit\Framework\TestCase;

class TokenizerTest extends TestCase
{
    public function providerIncomplete()
    {
        return [
            ['"foo;ba', 'r"'],
            ['-- ble', "!\r\n"],
            ["/* multi\r\n", " * line comment\r\n */"],
        ];
    }

    /**
     * @dataProvider providerIncomplete
     * @param $p1
     * @param $p2
     * @throws \LajosBencz\StreamParseSql\Exception\ParseException
     */
    public function testIncomplete($p1, $p2)
    {
        $t = new Tokenizer;
        $tokens = [];
        foreach($t->append($p1) as $token) {
            $tokens[] = $token;
        }
        $this->assertEquals(0, count($tokens));
        foreach($t->append($p2) as $token) {
            $tokens[] = $token;
        }
        $this->assertEquals(1, count($tokens));
        $t->reset();
    }


    public function testTokenizer()
    {
        $input = "-- comment!\r\nINSERT INTO tbl (foo, bar) /* comment\r\n aswell */ VALUES ('val'); UPDATE tbl SET foo='ba\'r' WHERE bar='bar';";
        $t = new Tokenizer;
        while(($result = $t->tokenize($input)) !== false) {
            //echo $result, PHP_EOL;
        }
        $this->assertEquals(26, count($t->tokens));
        $this->assertEquals(0, strlen($t->last_error));
    }
}
