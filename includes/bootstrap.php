<?php

namespace BeAmado\OjsMigrator;

if (!defined(__namespace__ . '\DIR_SEPARATOR')) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        define(
            __namespace__ . '\DIR_SEPARATOR',
            '\\'
        );
    } else {
        define(
            __namespace__ . '\DIR_SEPARATOR',
            '/'
        );
    }
}

require_once(dirname(__FILE__) . DIR_SEPARATOR
    . '..' . DIR_SEPARATOR
    . 'lib' . DIR_SEPARATOR
    . 'classes' . DIR_SEPARATOR
    . 'util' . DIR_SEPARATOR
    . 'FileSystemManager.php');

if (!defined(__namespace__ . '\BASE_DIR')) {
    define(
        __namespace__ . '\BASE_DIR',
        (new \BeAmado\OjsMigrator\Util\FileSystemManager())->parentDir(
            dirname(__FILE__)
        )
    );
}

if (!defined(__namespace__ . '\LIB_DIR')) {
    define(
        __namespace__ . '\LIB_DIR',
        (new \BeAmado\OjsMigrator\Util\FileSystemManager())->formPath(
            array(BASE_DIR, 'lib')
        )
    );
}

//require_once(LIB_DIR . '/classes/util/Autoloader.php');
require_once((new \BeAmado\OjsMigrator\Util\FileSystemManager())->formPath(
    array(LIB_DIR, 'classes', 'util', 'Autoloader.php')
));


(new \BeAmado\OjsMigrator\Util\Autoloader())->registerAutoload();
