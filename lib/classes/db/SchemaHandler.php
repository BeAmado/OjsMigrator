<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\Util\XmlHandler;
use \BeAmado\OjsMigrator\FiletypeHandler; //interface

class SchemaHandler implements FiletypeHandler
{
    /*protected function readXmlFile($filename)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->read($filename);
        return $xml;
    }*/

    public function createFromFile($filename)
    {
        return new Schema(
            (new XmlHandler())->createFromFile($filename)
        );
    }

    public function dumpToFile($filename, $content)
    {

    }
}
