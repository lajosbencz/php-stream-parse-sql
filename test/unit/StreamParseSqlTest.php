<?php


use LajosBencz\StreamParseSql\StreamParseSql;
use PHPUnit\Framework\TestCase;

class StreamParseSqlTest extends TestCase
{
    public function provideSqlFiles()
    {
        return [
            [__DIR__ . '/../fixture/000001.sql'],
        ];
    }

    /**
     * @dataProvider provideSqlFiles
     * @param string $filePath
     * @throws \LajosBencz\StreamParseSql\Exception
     */
    public function testStreamParseSql($filePath)
    {
        $parser = new StreamParseSql($filePath);
        $this->assertEquals(realpath($filePath), $parser->getFilePath());
        foreacH($parser->parse() as $line) {
            $this->assertNotEmpty($line);
        }
    }
}
