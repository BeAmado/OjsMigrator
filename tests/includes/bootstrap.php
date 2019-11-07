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

foreach (array('classes', 'interfaces', 'traits') as $directory) {
    foreach ($fm->listdir(dirname(__FILE__) . $sep . $directory) as $filename) {
        require_once($filename);
    }
}
unset($directory);
unset($filename);
unset($fm);
