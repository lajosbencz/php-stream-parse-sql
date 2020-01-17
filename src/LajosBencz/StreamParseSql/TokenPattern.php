<?php

namespace LajosBencz\StreamParseSql;


class TokenPattern
{
    public $name;
    public $regex;

    public function __construct(string $name, string $regex, string $delimiter = '/')
    {
        $this->name = $name;
        if(strlen($delimiter) > 0) {
            $regex = $delimiter . $regex . $delimiter;
        }
        $this->regex = $regex;
    }
}
