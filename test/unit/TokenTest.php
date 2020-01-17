<?php


use LajosBencz\StreamParseSql\Token;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    public function testCreat()
    {
        $t = new Token('TYPE', 'content');
        $this->assertEquals('TYPE', $t->type);
        $this->assertEquals('content', $t->content);
    }

    public function testAssemble()
    {
        $tokens = [];
        $a = ['foo', ' ', 'bar', '.', 'baz', '=', 'bax', ';'];
        foreach($a as $v) {
            $tokens[] = new Token('TYPE', $v);
        }
        $this->assertEquals(join('', $a), Token::Assemble(...$tokens));
    }
}
