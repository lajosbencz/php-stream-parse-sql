<?php

require_once __DIR__ . '/../vendor/autoload.php';


$dbBackup = __DIR__ . '/../../../../git.lazos.me/travelhood/models-v1.1.x/th_backup_000008.sql';


$parser = new LajosBencz\StreamParseSql\StreamParseSql($dbBackup);
foreach($parser->parse() as $sql) {
    echo $sql, PHP_EOL;
    echo sprintf("%0.4f", memory_get_usage(true) / 1024 / 1024), 'MB', PHP_EOL;
}

