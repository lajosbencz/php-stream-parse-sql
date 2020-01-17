<?php


namespace LajosBencz\StreamParseSql;


use Generator;

/**
 * @see https://github.com/martynshutt/php-tokenizer/blob/master/tokenizer.php
 */
class Tokenizer
{
    /** @var TokenPattern[] */
    protected $_patterns = [];

    /** @var string */
    protected $_buffer = '';

    /** @var int */
    protected $_offset = 0;

    public function __construct()
    {
        $this->pushPattern(new TokenPattern("STRING_", <<<'PATTERN'
^("|')(\\?.)*?(?<!\1)$
PATTERN));
        $this->pushPattern(new TokenPattern("COMMENT_SINGLE_", <<<'PATTERN'
^\-\-\s[^\r\n]*$
PATTERN));
        $this->pushPattern(new TokenPattern("COMMENT_MULTI_", <<<'PATTERN'
^\/\*[\s\S]+(?<!\*\/)$
PATTERN));

        $this->pushPattern(new TokenPattern("COMMENT_SINGLE", <<<'PATTERN'
^--\s(.*)(\r)?\n
PATTERN));
        $this->pushPattern(new TokenPattern("COMMENT_MULTI", <<<'PATTERN'
^\/\*(\r|\n|.)*\*\/
PATTERN));

        $this->pushPattern(new TokenPattern("STRING", <<<'PATTERN'
^("|')(\\?.)*?\1
PATTERN));

        $this->pushPattern(new TokenPattern('DELIMITER', '^[;]'));
        $this->pushPattern(new TokenPattern("BRACKET_OPEN", "^\("));
        $this->pushPattern(new TokenPattern("BRACKET_CLOSE", "^\)"));
        $this->pushPattern(new TokenPattern('IDENTIFIER', '^[a-zA-Z_][a-zA-Z0-9_]*'));
        $this->pushPattern(new TokenPattern('WHITESPACE', '^[\s]'));
        $this->pushPattern(new TokenPattern('EXPRESSION', '^[\S]+'));
        $this->pushPattern(new TokenPattern("NEWLINE", '^(\r)?\n'));
        $this->pushPattern(new TokenPattern('ANY', '^.'));
    }

    public function clearPatterns(): void
    {
        $this->_patterns = [];
    }

    public function pushPattern(TokenPattern $pattern): self
    {
        $this->_patterns[] = $pattern;
        return $this;
    }

    /**
     * @return TokenPattern[]
     */
    public function getPatterns(): array
    {
        return $this->_patterns;
    }

    public function reset(): void
    {
        $this->_buffer = '';
        $this->_offset = 0;
    }

    /**
     * @param $chunk
     * @return Token[]|Generator
     * @throws Exception\ParseException
     */
    public function append($chunk): Generator
    {
        $input = $this->_buffer . $chunk;
        $offset = 0;
        $tokens = [];
        while (strlen($input) > 0) {
            $hit = false;
            foreach ($this->_patterns as $pattern) {
                if (@preg_match($pattern->regex, $input, $matches, PREG_OFFSET_CAPTURE)) {
                    $hit = true;
                    $matchText = $matches[0][0];
                    $matchOffset = $matches[0][1];
                    $matchLength = strlen($matchText);
                    $offset += $matchLength;
                    $this->_offset += $matchLength;
                    $token = new Token($pattern, $matchText, $this->_offset + $offset + $matchOffset, $matchLength);
                    switch ($token->pattern->name) {
                        case 'STRING_':
                        case 'COMMENT_SINGLE_':
                        case 'COMMENT_MULTI_':
                            $this->_buffer = $matchText;
                            return;
                        default:
                            if ($token->pattern->name === 'DELIMITER') {
                                foreach ($tokens as $t) {
                                    yield $t;
                                }
                                yield $token;
                                $tokens = [];
                            }
                            $input = substr($input, $matchLength);
                            $tokens[] = $token;
                            break;
                    }
                    break;
                }
            }
            if (!$hit) {
                throw new Exception\ParseException($input);
            }
        }
        foreach ($tokens as $t) {
            yield $t;
        }
        $this->_offset += $offset;
    }

}
