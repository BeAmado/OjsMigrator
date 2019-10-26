<?php

namespace BeAmado\OjsMigrator\Util;
use \BeAmado\OjsMigrator\FiletypeHandler;
use \BeAmado\OjsMigrator\Registry;

class JsonHandler implements FiletypeHandler
{
    /**
     * Creates an object from a json file
     *
     * @param $filename string
     * @return \BeAmado\OjsMigrator\MyObject
     */
    public function createFromFile($filename)
    {
        return Registry::get('MemoryManager')->create(
            \json_decode(
                Registry::get('FileHandler')->read($filename),
                true
            )
        );
    }

    /**
     * Dumps the object into a json file.
     *
     * @param $filename string
     * @param $obj \BeAmado\OjsMigrator\MyObject
     * @return boolean
     */
    public function dumpToFile($filename, $obj)
    {
        return Registry::get('FileHandler')->write(
            $filename,
            \json_encode($obj->toArray())
        );
    }
}
