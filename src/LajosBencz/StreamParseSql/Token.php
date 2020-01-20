<?php
declare(strict_types=1);

namespace LajosBencz\StreamParseSql;


class Token
{
    /**
     * Assembles a list of tokens to an SQL command
     * @param Token ...$tokens
     * @return string
     */
    public static function Assemble(Token ...$tokens): string
    {
        $sql = '';
        foreach($tokens as $t) {
            $sql.= $t->content;
        }
        $sql = preg_replace('/[\s\t]+/', ' ', $sql);
        if(!preg_match('/;[\r\n\s]*$/', $sql)) {
            $sql.= ';';
        }
        return $sql;
    }

    /** @var string */
    public $type = '';

    /** @var string */
    public $content = '';

    /**
     * Represents a token in memory
     * @param string $type
     * @param string $content
     */
    public function __construct(string $type, string $content)
    {
        $this->type = $type;
        $this->content = $content;
    }

    public function __toString()
    {
        return $this->content;
    }
}
