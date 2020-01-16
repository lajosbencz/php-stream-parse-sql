<?php

use PHPUnit\Framework\TestCase;


use LajosBencz\StreamParseSql\Buffer;

class BufferTest extends TestCase
{
    public function provideBuffer()
    {
        return [
            [["-- yeye\r\nSELECT * FROM test; INSERT"," INTO test (name) VALUES ('f\'oo;bar!')"], 2],
        ];
    }

    /**
     * @dataProvider provideBuffer
     * @param array $lines
     * @param int $commandCount
     */
    public function testBuffer(array $lines, int $commandCount)
    {
        $buffer = new Buffer;
        $n = 0;
        foreach($lines as $line) {
            $command = $buffer->append($line);
            if($command) {
                $n++;
            }
        }
        $this->assertEquals($commandCount, $n);
    }
}
