<?php


namespace LajosBencz\StreamParseSql;

const DEFAULT_DELIMITER = ';';

trait DelimiterAwareTrait
{
    protected $_delimiter;

    public function setDelimiter(string $delimiter=DEFAULT_DELIMITER): void
    {
        $this->_delimiter = $delimiter;
    }

    public function getDelimiter(): string
    {
        return $this->_delimiter;
    }
}