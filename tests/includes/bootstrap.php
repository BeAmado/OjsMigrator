<?php

$sep = '/';

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $sep = '\\';
}

require_once(implode($sep, [
    dirname(__FILE__),
    '..',
    '..',
    'includes',
    'bootstrap.php',
]));

require_once(implode($sep, [
    dirname(__FILE__),
    'classes',
    'Autoloader.php',
]));

unset($sep);

(new \BeAmado\OjsMigrator\Test\Autoloader())->registerAutoload();
