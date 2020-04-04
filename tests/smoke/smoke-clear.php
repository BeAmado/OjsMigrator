<?php

$fsm = \BeAmado\OjsMigrator\Registry::get('FileSystemManager');
// compress the entities dir into a tar.gz file
\BeAmado\OjsMigrator\Registry::get('ArchiveManager')->tar(
    'cz',
    $fsm->formPathFromBaseDir('data'),
    $fsm->formPathFromBaseDir(array(
        'tests',
        '_data',
        'sandbox',
    ))
);

(new \BeAmado\OjsMigrator\Test\OjsScenarioHandler())->tearDownStage();
