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

$fsm = new \BeAmado\OjsMigrator\Util\FileSystemManager();

foreach (array(
    'ConfigPreprocessor',
    'OjsScenarioTester',
) as $classname) {
    require_once(
        $fsm->formPathFromBaseDir(array(
            'tests',
            'include',
            'classes',
            $classname . '.php'
        ))
        /*dirname(__FILE__)
        . $sep . '..'
        . $sep . 'includes'
        . $sep . 'classes'
        . $sep . $classname . '.php'*/
    );
}

$dbDriver = (new \BeAmado\OjsMigrator\Util\FileHandler())->read(
    $fsm->formPathFromBaseDir(array(
        'tests',
        'dbdriver'
    ))
);

(new \BeAmado\OjsMigrator\OjsScenarioTester())->setUpStage(
    in_array($dbDriver, array('mysql', 'sqlite')) 
        ? array('dbDriver' => $dbDriver)
        : null
);
