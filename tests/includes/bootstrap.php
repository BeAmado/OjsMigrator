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

$vars->set(
    'filesDirLineNumber',
    -1
);

for ($i = 0; $i < count($vars->get('template')->listValues()); $i++) {
    if (
        substr(
            $vars->get('template')->get($i)->getValue(), 
            0, 
            11
        ) === 'files_dir ='
    ) {
        $vars->set('filesDirLineNumber', $i);
        break;
    }
}

$vars->get('template')->set(
    $vars->get('filesDirLineNumber')->getValue(),
    str_replace(
        '[ojs2_dir]',
        $vars->get('ojs2Dir')->getValue(),
        $vars->get('template')
             ->get($vars->get('filesDirLineNumber')->getValue())
             ->getValue()
    )
);

file_put_contents(
    $vars->get('ojs2ConfigFile')->getValue(),
    $vars->get('template')->toArray()
);

(new \BeAmado\OjsMigrator\Util\MemoryManager())->destroy($vars);
unset($vars);
unset($sep);
unset($fm);
