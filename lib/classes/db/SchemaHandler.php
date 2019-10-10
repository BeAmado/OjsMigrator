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
    protected function formatTableDefinitionArray($obj)
    {
        if ($obj->get('name')->getValue() !== 'table') {
            return;
        }

        $def = array();
        $def = 

    }

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
