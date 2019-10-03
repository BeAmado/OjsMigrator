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
