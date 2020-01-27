<?php

$sep = '/';

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $sep = '\\';
}

require_once(
    dirname(__FILE__)
    . $sep . '..'
    . $sep . '..'
    . $sep . 'includes'
    . $sep . 'bootstrap.php'
);

$fm = new \BeAmado\OjsMigrator\Util\FileSystemManager();

require_once(
    dirname(__FILE__)
    . $sep . 'classes'
    . $sep . 'EntityMock.php'
);

foreach (array('interfaces', 'traits', 'classes') as $directory) {
    foreach ($fm->listdir(dirname(__FILE__) . $sep . $directory) as $filename) {
        require_once($filename);
    }
}
unset($directory);
unset($filename);
unset($fm);
