<?php

$sep = '/';

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $sep = '\\';
}

require_once(
    '..' 
    . $sep . '..'
    . $sep . 'includes' 
    . $sep . 'bootstrap.php'
);

foreach (array(
    'ConfigPreprocessor',
    'OjsScenarioTester',
) as $classname) {
    require_once(
        '..'
        . $sep . 'includes'
        . $sep . 'classes'
        . $sep . $classname . '.php'
    );
}

(new \BeAmado\OjsMigrator\OjsScenarioTester())->setUpStage();
