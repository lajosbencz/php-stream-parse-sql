<?php
declare(strict_types=1);

namespace LajosBencz\StreamParseSql;


use Generator;

class Tokenizer
{
    protected static $PATTERNS = [
        // INCOMPLETE STRINGS
        'STRING_' => <<<'PATTERN'
/^'[^'\\]*(\\.[^'\\]*)*$/
PATTERN
        ,
        // INCOMPLETE STRINGS
        'STRING2_' => <<<'PATTERN'
/^"[^"\\]*(\\.[^"\\]*)*$/
PATTERN
        ,
        // INCOMPLETE SINGLE LINE COMMENTS
        'COMMENT_DASH_' => <<<'PATTERN'
/^\-(\-)?((\s[^\r\n]*))$/
PATTERN
        ,
        // INCOMPLETE SINGLE LINE COMMENTS
        'COMMENT_HASH_' => <<<'PATTERN'
/^[#][^\r\n]*$/
PATTERN
        ,
        // CONDITIONAL COMMENT
        'COMMENT_CONDITION' => <<<'PATTERN'
/^\/\*[\!\+][^*]*\*+(?:[^\/*][^*]*\*+)*\//
PATTERN
        ,
        // MULTI LINE COMMENT
        'COMMENT_MULTI' => <<<'PATTERN'
/^\/\*[^*]*\*+(?:[^\/*][^*]*\*+)*\//
PATTERN
        ,
        // INCOMPLETE CONDITIONAL COMMENT
        'COMMENT_CONDITION_' => <<<'PATTERN'
/^\/\*[\!\+][^*]*$/
PATTERN
        ,
        // INCOMPLETE MULTI LINE COMMENTS
        'COMMENT_MULTI_' => <<<'PATTERN'
/^\/\*[\s\S]+(?<!\*\/)$/
PATTERN
        ,
        // STRING LITERAL
        'STRING' => <<<'PATTERN'
/^'[^'\\]*(\\.[^'\\]*)*'/
PATTERN
        ,
        // STRING2 LITERAL
        'STRING2' => <<<'PATTERN'
/^"[^"\\]*(\\.[^"\\]*)*"/
PATTERN
        ,
        // SINGLE LINE COMMENT
        'COMMENT_HASH' => <<<'PATTERN'
/^[#](.*)(\r)?\n/
PATTERN
        ,
        // SINGLE LINE COMMENT
        'COMMENT_DASH' => <<<'PATTERN'
/^--\s(.*)(\r)?\n/
PATTERN
        ,

        // SQL COMMAND DELIMITER
        'DELIMITER' => '/^;/',
        // EXPRESSION SEPARATOR
        'SEPARATOR' => '/^[\.,]/',
        // NEW LINE NIX/WIN
        'NEWLINE' => '/^(\r)?\n/',
        // WHITESPACE CHARACTERS
        'WHITESPACE' => '/^[\s]+/',
        // GROUPING CHARACTERS
        'PARENTHESIS' => '/^[\(\)\[\]\`]/',

        // THE REST OF THE STRING....
        'EXPRESSION' => <<<'PATTERN'
/^[^\s;\(\)\[\]\`\r\n'"]+/
PATTERN
        ,
    ];

    /** @var string */
    protected $_delimiter = StreamParseSql::DEFAULT_DELIMITER;

    /** @var string */
    private $_buffer = '';

    /** @var string */
    private $_openToken = '';

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
                        case 'STRING2_':
                        case 'COMMENT_DASH_':
                        case 'COMMENT_HASH_':
                        case 'COMMENT_MULTI_':
                        case 'COMMENT_CONDITION_':
                            $this->_buffer = $input;
                            $input = '';
                            $this->_openToken = $patternType;
                            if($eof) {
                                $this->_tokens[] = $token;
                            }
                            break;
                        default:
                            $this->_openToken = '';
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
