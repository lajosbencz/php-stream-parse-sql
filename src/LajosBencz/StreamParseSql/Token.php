<?php

namespace LajosBencz\StreamParseSql;


class Token
{
    public $pattern = null;
    public $content = '';
    public $offset = 0;
    public $length = 0;

    public function __construct(TokenPattern $pattern, string $content, int $offset, int $length)
    {
        $this->pattern = $pattern;
        $this->content = $content;
        $this->offset = $offset;
        $this->length = $length;
    }

    public static function Assemble(Token ...$tokens): string
    {
        $sql = '';
        foreach($tokens as $t) {
            $sql.= $t->content;
        }
        return $sql;
    }
}
