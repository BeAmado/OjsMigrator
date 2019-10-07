<?php

//require the following files
array_map(function($filename){
    require_once(dirname(__FILE__) . '/' . $filename);
}, array(
    'TestStub.php', 
    'StubInterface.php', 
    'WorkWithFiles.php',
    'WorkWithXmlSchema.php',
));

require_once(dirname(__FILE__) . '/../../includes/bootstrap.php');

// format the config.inc.php file
$sep = \BeAmado\OjsMigrator\DIR_SEPARATOR;

$ojs2Dir = (new \BeAmado\OjsMigrator\Util\FileSystemManager())->parentDir(
    dirname(__FILE__)
) // OjsMigrator/tests
    . $sep . '_data' // /_data
    . $sep . 'ojs2'; // result -> OjsMigrator/tests/_data/ojs2

(new \BeAmado\OjsMigrator\Util\ArchiveManager)->tar(
    'xzf', 
    $ojs2Dir . '.tar.gz',
    (new \BeAmado\OjsMigrator\Util\FileSystemManager())->parentDir($ojs2Dir)
);

$ojs2ConfigFile = $ojs2Dir 
    . $sep . 'public_html' 
    . $sep . 'config.inc.php';

$ojs2ConfigTemplate = $ojs2ConfigFile . '.TEMPLATE';

$template = file($ojs2ConfigTemplate);

$filesDirLineNumber = -1;

for ($i = 0; $i < count($template); $i++) {
    if (substr($template[$i], 0, 11) == 'files_dir =') {
        $filesDirLineNumber = $i;
    }
}

$template[$filesDirLineNumber] = str_replace(
    '[ojs2_dir]',
    $ojs2Dir,
    $template[$filesDirLineNumber]
);

file_put_contents(
    $ojs2ConfigFile,
    $template
);
