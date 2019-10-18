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

foreach (array('interfaces', 'traits') as $directory) {
    foreach ($fm->listdir(dirname(__FILE__) . $sep . $directory) as $filename) {
        require_once($filename);
    }
}
unset($directory);
unset($filename);

$vars = (new \BeAmado\OjsMigrator\Util\MemoryManager())->create();
$vars->set('sep', $sep);

// format the config.inc.php file
$vars->set(
    'ojs2Dir', 
    $fm->parentDir(dirname(__FILE__)) // OjsMigrator/tests
        . $sep . '_data' // /_data
        . $sep . 'ojs2' // result -> OjsMigrator/tests/_data/ojs2
);

(new \BeAmado\OjsMigrator\Util\ArchiveManager)->tar(
    'xzf', 
    $vars->get('ojs2Dir')->getValue() . '.tar.gz',
    $fm->parentDir($vars->get('ojs2Dir')->getValue())
);

(new \BeAmado\OjsMigrator\Util\MemoryManager())->destroy($fm);

$vars->set(
    'ojs2ConfigFile', 
    $vars->get('ojs2Dir')->getValue()
        . $sep . 'public_html' 
        . $sep . 'config.inc.php'
);

$vars->set(
    'ojs2ConfigTemplate',
    $vars->get('ojs2ConfigFile')->getValue() . '.TEMPLATE'
);

$vars->set(
    'template', 
    file($vars->get('ojs2ConfigTemplate')->getValue())
);

function setFilesDir($str, $vars)
{
    return str_replace(
        '[ojs2_dir]',
        $vars->get('ojs2Dir')->getValue(),
        $str
    );
}

function setDbName($vars)
{
    switch(getDbDriver()) {
        case 'sqlite':
            return $vars->get('ojs2Dir')->getValue()
                . $vars->get('sep')->getValue() . 'tests_ojs.db';
        case 'mysql':
            return 'tests_ojs';
    }
}

function getDbDriver()
{
    if (array_search('pdo_sqlite', get_loaded_extensions())) {
        return 'sqlite';
    } else if (array_search('pdo_mysql', get_loaded_extensions())) {
        return 'mysql';
    }
}

function isInTheLine($id, $str)
{
    switch($id) {
        case 'files_dir':
            return substr($str, 0, 11) === 'files_dir =';
        case 'name':
            return substr($str, 0, 6) === 'name =';
        case 'driver':
            return substr($str, 0, 8) === 'driver =';
    }

    return false;
}

$config = array();

foreach ($vars->get('template')->toArray() as $line) {
    if (isInTheLine('files_dir', $line)) {
        $config[] = setFilesDir($line, $vars) . PHP_EOL;
    } else if (isInTheLine('name', $line)) {
        $config[] = 'name = ' . setDbName($vars) . PHP_EOL;
    } else if (isInTheLine('driver', $line)) {
        $config[] = 'driver = ' . getDbDriver() . PHP_EOL;
    } else {
        $config[] = $line;
    }
}

file_put_contents(
    $vars->get('ojs2ConfigFile')->getValue(),
    $config
);

\BeAmado\OjsMigrator\Registry::clear();

\BeAmado\OjsMigrator\Registry::set(
    'configFile',
    $vars->get('ojs2ConfigFile')->getValue()
);

\BeAmado\OjsMigrator\Registry::set(
    'ConfigHandler',
    new \BeAmado\OjsMigrator\Util\ConfigHandler()
);

var_dump(\BeAmado\OjsMigrator\Registry::listKeys());

(new \BeAmado\OjsMigrator\Util\MemoryManager())->destroy($vars);
unset($vars);
(new \BeAmado\OjsMigrator\Util\MemoryManager())->destroy($config);
unset($config);

unset($sep);
unset($fm);
