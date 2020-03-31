<?php

$sep = '/';

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $sep = '\\';
}

// the tests bootstrap. It enables autoloading the classes in the Test namespace 
require_once(
    dirname(__FILE__)
    . $sep . '..'
    . $sep . 'includes' 
    . $sep . 'bootstrap.php'
);

$fsm = new \BeAmado\OjsMigrator\Util\FileSystemManager();
$dbDriverFile = $fsm->formPathFromBaseDir(array(
    'tests',
    'dbdriver'
));

$dbDriver = $fsm->fileExists($dbDriverFile)
    ? (new \BeAmado\OjsMigrator\Util\FileHandler())->read($dbDriverFile)
    : 'sqlite';

(new \BeAmado\OjsMigrator\Test\OjsScenarioHandler())->setUpStage(array(
    'dbDriver' => in_array($dbDriver, array('mysql', 'sqlite')) 
        ? $dbDriver
        : 'sqlite',
    'createTables' => array(
        'journals',
    ),
));

(new \BeAmado\OjsMigrator\Test\FixtureHandler())->createSeveral(array(
    'journals' => array(
        'test_journal',
    ),
    'users' => array(
        'ironman',
        'hulk',
        'batman',
        'thor',
        'greenlantern',
    ),
    'review_forms' => array(
        'first',
        'second',
    ),
    'sections' => array(
        'sports',
        'sciences',
    ),
    'issues' => array(
        '2011',
        '2015',
    ),
    'groups' => array(),
    'announcements' => array(),
    'submissions' => array(),
), true);
