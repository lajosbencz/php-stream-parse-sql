<?php


namespace LajosBencz\StreamParseSql;


/**
 * @see https://github.com/martynshutt/php-tokenizer/blob/master/tokenizer.php
 * @property-read array tokens
 * @property-read string last_error
 */
class Tokenizer
{
    private $_patterns = [];
    private $_length = 0;
    private $_tokens = [];
    private $_delimiter = '';
    private $_last_error = '';

    public function __construct($delimiter = "#")
    {
        $this->_delimiter = $delimiter;
    }

    /**
     * Add a regular expression to the Tokenizer
     *
     * @param string $name name of the token
     * @param string $pattern the regular expression to match
     */
    public function add($name, $pattern)
    {
        $this->_patterns[$this->_length]['name'] = $name;
        $this->_patterns[$this->_length]['regex'] = $pattern;
        $this->_length++;
    }

    /**
     * Tokenizes a reference to an input string,
     * removing matches from the beginning of the string
     *
     * @param string &$input the input string to tokenize
     *
     * @return boolean|string returns the matched token on success, boolean false on failure
     */
    public function tokenize(&$input)
    {
        for ($i = 0; $i < $this->_length; $i++) {
            if (@preg_match($this->_patterns[$i]['regex'], $input, $matches)) {


                $this->_tokens[] = [
                    'name' => $this->_patterns[$i]['name'],
                    'token' => $matches[0],
                    'offset' => $i,
                ];

                //remove last found token from the $input string
                //we use preg_quote to escape any regular expression characters in the matched input
                $input = trim(preg_replace($this->_delimiter . "^" . preg_quote($matches[0], $this->_delimiter) . $this->_delimiter, "", $input));

                return $matches[0];
            } elseif (preg_match($this->_patterns[$i]['regex'], $input, $matches) === false) {
                $this->_last_error = 'Error occured at $_patterns[' . $i . ']';
                return false;
            }
        }
        return false;
    }

    public function __get($item)
    {
        switch ($item) {
            case 'tokens':
                return $this->_tokens;
            case 'last_error':
                return $this->_last_error;
        }
        return null;
    }

    public function reset()
    {
        $this->_tokens = [];
        $this->_last_error = '';
    }
}