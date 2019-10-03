<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\FiletypeHandler;

class SchemaHandler implements FiletypeHandler
{
    public function createFromFile($filename)
    {
        return new \BeAmado\OjsMigrator\MyObject(array(
            'journals' => null,
        ));
    }

    public function dumpToFile($filename, $content)
    {

    }
}
