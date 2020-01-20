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
        foreach($t->append($p2, true) as $token) {
            $tokens[] = $token;
        }
        $this->assertEquals(1, count($tokens));
        $t->reset();
    }


    public function testTokenizer()
    {
        $input = "-- comment!\r\nINSERT INTO tbl (foo, bar) /* comment\r\n aswell */ VALUES ('val'); UPDATE tbl SET foo='ba\'r' WHERE bar='bar';";
        $t = new Tokenizer;
        $tn = 0;
        foreach($t->append($input, true) as $token) {
            $this->assertNotEmpty($token);
            $tn++;
        }
        $this->assertEquals(36, $tn);
    }

    public function provideEscapedString()
    {
        return [
            [
                <<<SQL
'Air Test
SQL
                ,
                ['STRING_'],
            ],
            [
                <<<SQL
"test", '\\\\', 'Jayrow', ';;', "string";
SQL
                ,
                ['STRING2', 'SEPARATOR', 'WHITESPACE', 'STRING', 'SEPARATOR', 'WHITESPACE', 'STRING', 'SEPARATOR', 'WHITESPACE', 'STRING', 'SEPARATOR', 'WHITESPACE', 'STRING2', 'DELIMITER'],
            ],
        ];
    }

    /**
     * @dataProvider provideEscapedString
     * @param string $sql
     * @param string[] $expectedTokens
     * @throws \LajosBencz\StreamParseSql\Exception\ParseException
     */
    public function testEscapedString(string $sql, array $expectedTokens)
    {
        $t = new Tokenizer;
        $actualTokens = [];
        $out = '';
        foreach($t->append($sql, true) as $token) {
            $actualTokens[] = $token->type;
            $out.= $token;
        }
        $this->assertEquals($expectedTokens, $actualTokens);
    }
}
