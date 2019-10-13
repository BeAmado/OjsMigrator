<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\Util\XmlHandler;
use \BeAmado\OjsMigrator\FiletypeHandler; //interface

class SchemaHandler implements FiletypeHandler
{
    /**
     * Gets the name of the table to be defined
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return string
     */
    protected function getTableName($obj)
    {
        if (
            !$obj->get('attributes')->hasAttribute('name') ||
            $obj->get('name')->getValue() !== 'table'
        ) {
            return;
        }

        return $obj->get('attributes')->get('name')->getValue();
    }

    protected function getColumn

    /**
     * Gets the datatype of the column.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return string
     */
    protected function getDataType($obj)
    {
        switch(\strtoupper(
            \substr($obj->get('attributes')->get('type')->getValue(), 0, 1)
        )) { //The first character of the type
            case 'I':
                return 'integer';

            case 'F':
                return 'float';

            case 'X':
            case 'C':
                return 'string';

            default:
                return 'string';
        }
    }

    protected function getSqlType($obj)
    {
        $sql
    }

    protected function formatTableDefinitionArray($obj)
    {
        if ($obj->get('name')->getValue() !== 'table') {
            return;
        }

        $def = array();
        $def['name'] = $this->getTableName($obj);

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
