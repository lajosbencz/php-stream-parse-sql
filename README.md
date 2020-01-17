# php-stream-parse-sql
PHP library to parse large SQL files command-by-command

It's not a real SQL parser though, it only cares about command integrity, but not syntax.

### Install:

```
composer require lajosbencz/stream-parse-sql
```

### Usage:

```php
$parser = new LajosBencz\StreamParseSql\StreamParseSql("./my/large/file.sql");
$parser->onProgress(function($position, $size) {
    echo ($position / $size) * 100, PHP_EOL;
});
foreacH($parser->parse() as $sqlCommand) {
    /** @var stdClass $myDb some database adapter */
    $myDb->execute($sqlCommand);
}
```

### Todo:
 * Extensively test if any input can produce a malformed command
