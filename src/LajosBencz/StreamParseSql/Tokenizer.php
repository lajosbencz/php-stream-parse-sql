<?php
declare(strict_types=1);

namespace LajosBencz\StreamParseSql;


use Generator;

class Tokenizer
{
    protected static $PATTERNS = [
        // INCOMPLETE STRINGS
        'STRING_' => <<<'PATTERN'
/^("|')[^"']*$/
PATTERN
        ,
        // INCOMPLETE SINGLE LINE COMMENTS
        'COMMENT_SINGLE_' => <<<'PATTERN'
/^\-\-\s[^\r\n]*$/
PATTERN
        ,
        // MULTI LINE COMMENT
        'COMMENT_MULTI' => <<<'PATTERN'
/^\/\*(\r|\n|.)*\*\//
PATTERN
        ,
        // INCOMPLETE MULTI LINE COMMENTS
        'COMMENT_MULTI_' => <<<'PATTERN'
/^\/\*[\s\S]+(?<!\*\/)$/
PATTERN
        ,
        // STRING LITERAL
        'STRING' => <<<'PATTERN'
/^("|')(\\?.)*?\1/
PATTERN
        ,
        // SINGLE LINE COMMENT
        'COMMENT_SINGLE' => <<<'PATTERN'
/^--\s(.*)(\r)?\n/
PATTERN
        ,

        // SQL COMMAND DELIMITER
        'DELIMITER' => '/^;/',
        // EXPRESSION SEPARATOR
        'SEPARATOR' => '/^[\.]/',
        // NEW LINE NIX/WIN
        'NEWLINE' => '/^(\r)?\n/',
        // WHITESPACE CHARACTERS
        'WHITESPACE' => '/^[\s]+/',
        // GROUPING CHARACTERS
        'PARENTHESIS' => '/^[\(\)\[\]\`]/',
        // THE REST OF THE STRING....
        'EXPRESSION' => '/^[^\s;\(\)\[\]\`\r\n]+/',
    ];

    /** @var string */
    protected $_delimiter = StreamParseSql::DEFAULT_DELIMITER;

    /** @var string */
    private $_buffer = '';

    /** @var Token[] */
    private $_tokens = [];


    /**
     * Tokenizer accepts SQL commands in string chunks and yields back one command at a time
     * @param string $delimiter THe command delimiter used in the SQL file
     */
    public function __construct(string $delimiter = StreamParseSql::DEFAULT_DELIMITER)
    {
        $this->_delimiter = $delimiter;
        self::$PATTERNS['DELIMITER'] = '/^'.preg_quote($delimiter, '/').'/';
    }

    /**
     * Gets the command delimiter
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->_delimiter;
    }

    /**
     * Resets the internal state
     */
    public function reset(): void
    {
        $this->_buffer = '';
        $this->_tokens = [];
    }

    /**
     * Appends the next chunk to the buffer and yields all parsed commands from it
     * @param string $chunk Next command chunk
     * @param bool $eof Is this the last chunk?
     * @return Token[]|Generator
     * @throws Exception\ParseException
     */
    public function append(string $chunk, bool $eof = false): Generator
    {
        $input = $this->_buffer . $chunk;
        $offset = 0;
        while (strlen($input) > 0) {
            $hit = false;
            foreach (self::$PATTERNS as $patternType => $patternRegex) {
                if (@preg_match($patternRegex, $input, $matches, PREG_OFFSET_CAPTURE)) {
                    $hit = true;
                    $matchText = $matches[0][0];
                    //$matchOffset = $matches[0][1];
                    $matchLength = strlen($matchText);
                    $offset += $matchLength;
                    $token = new Token($patternType, $matchText);
                    switch ($patternType) {
                        case 'STRING_':
                        case 'COMMENT_SINGLE_':
                        case 'COMMENT_MULTI_':
                            $this->_buffer = $matchText;
                            return;
                        default:
                            $this->_tokens[] = $token;
                            $this->_buffer = '';
                            if ($patternType == 'DELIMITER') {
                                foreach ($this->_tokens as $t) {
                                    yield $t;
                                }
                                $this->_tokens = [];
                            }
                            $input = substr($input, $matchLength);
                            break;
                    }
                    break;
                }
            }
            if (!$hit) {
                throw new Exception\ParseException($input);
            }
        }
        if ($eof) {
            foreach ($this->_tokens as $token) {
                yield $token;
            }
            $this->reset();
        }
    }

}
