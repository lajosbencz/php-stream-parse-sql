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
        $ln = 0;
        foreacH($parser->parse() as $line) {
            $this->assertNotEmpty($line);
            //$parsed.= $line . "\r\n";
            $ln++;
        }
        $this->assertEquals(40, $ln);
    }

    public function testIncomplete()
    {
        $f = sys_get_temp_dir() . '/temp-' . uniqid() . '.sql';
        $p1 = "SELECT 'foo;";
        $p2 = "bar' FROM test LIMIT 1";
        file_put_contents($f, $p1.$p2);
        $p = new StreamParseSql($f, StreamParseSql::DEFAULT_DELIMITER, strlen($p1));
        foreach ($p->parse() as $sql) {
            $this->assertNotEmpty($sql);
        }
        unlink($f);
    }

    public function testComments()
    {
        $file = __DIR__ . '/../fixture/comments.sql';
        $t = new StreamParseSql($file);
        $n = 0;
        foreach($t->parse() as $sql) {
            $n++;
        }
        $this->assertEquals(0, $n);
    }

    public function testConditionalComments()
    {
        $file = __DIR__ . '/../fixture/conditional-comments.sql';
        $t = new StreamParseSql($file);
        $n = 0;
        foreach($t->parse() as $sql) {
            $n++;
        }
        $this->assertGreaterThan(0, $n);
    }

    public function testLongValues()
    {
        $file = __DIR__ . '/../fixture/long_value.sql';
        $t = new StreamParseSql($file);
        $n = 0;
        foreach($t->parse() as $sql) {
            $n++;
        }
        $this->assertGreaterThan(0, $n);
    }


}
