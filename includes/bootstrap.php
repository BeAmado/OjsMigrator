<?php

namespace BeAmado\OjsMigrator;

require_once(dirname(__FILE__) . '/../lib/classes/util/FileSystemManager.php');

if (!defined(__namespace__ . '\BASE_DIR')) {
    define(
        __namespace__ . '\BASE_DIR',
        (new \BeAmado\OjsMigrator\Util\FileSystemManager())->parentDir(dirname(__FILE__))
    );
}

if (!defined(__namespace__ . '\LIB_DIR')) {
    define(
        __namespace__ . '\LIB_DIR',
        BASE_DIR . '/lib'
    );
}

require_once(LIB_DIR . '/classes/util/Autoloader.php');

(new \BeAmado\OjsMigrator\Util\Autoloader())->registerAutoload();
