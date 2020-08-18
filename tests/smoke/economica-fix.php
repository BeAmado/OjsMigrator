#!/usr/bin/env php
<?php

namespace BeAmado\OjsMigrator;

try {
    require_once('smoke-bootstrap.php');

    $fixer = new Extension\EconomicaFixer(array(
        'OjsDir' => Registry::get('OjsDir'),
        'clearRegistry' => false,
    ));
    
    $fixer->translateXmlMapping('economicaDataMappings.xml');
} catch (\Exception $e) {
    echo "\n\n\nThrew the following exception: \n";
    var_dump($e);
    echo "\n\n\n";
} finally {
    require_once('smoke-clear.php');
}
