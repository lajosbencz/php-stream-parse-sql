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
        $parsed = '';
        $parser = new StreamParseSql($filePath);
        $this->assertEquals(realpath($filePath), $parser->getFilePath());
        foreacH($parser->parse() as $line) {
            $this->assertNotEmpty($line);
            $parsed.= $line . "\r\n";
        }
        file_put_contents($filePath.'.check.sql', $parsed);
    }

    public function testIncomplete()
    {
        $f = __DIR__ . '/../fixture/temp.sql';
        $p1 = "SELECT 'foo;";
        $p2 = "bar' FROM test LIMIT 1";
        file_put_contents($f, $p1.$p2);
        $p = new StreamParseSql($f, StreamParseSql::DEFAULT_DELIMITER, strlen($p1));
        foreach ($p->parse() as $sql) {
            $this->assertNotEmpty($sql);
        }
        unlink($f);
    }
}
