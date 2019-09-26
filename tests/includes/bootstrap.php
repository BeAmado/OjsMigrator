<?php

//require the following files
array_map(function($filename){
    require_once(dirname(__FILE__) . '/' . $filename);
}, ['TestStub.php', 'StubInterface.php', 'WorkWithFiles.php']);

require_once(dirname(__FILE__) . '/../../includes/bootstrap.php');
