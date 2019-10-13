<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\Util\XmlHandler;
use \BeAmado\OjsMigrator\FiletypeHandler; //interface

class SchemaHandler implements FiletypeHandler
{
    /**
     * Checks whether or not the given object has the specified name.
     *
     * @param \BeAmado\OjsMigrator\MyObject $o
     * @param string $name
     * @return boolean
     */
    protected function nameIs($o, $name)
    {
        if (
            !\is_a($o, \BeAmado\OjsMigrator\MyObject::class) ||
            !$o->hasAttribute('name')
        ) {
            return false;
        }

        return \strtolower($o->get('name')->getValue()) === \strtolower($name);
    }

    /**
     * Check whether or not the given obj is defining a table.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return boolean
     */
    protected function isTable($obj)
    {
        return $this->nameIs($obj, 'table');
    }

    /**
     * Checks whether or not the given object is defining a table column.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return boolean
     */
    protected function isColumn($obj)
    {
        return $this->nameIs($obj, 'field');
    }

    /**
     * Gets the specified attribute which must be present in the attributes 
     * array of the given object.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @param string $attr
     * @return string
     */
    protected function getAttribute($obj, $attr)
    {
        if (
            !\is_a($obj, \BeAmado\OjsMigrator\MyObject::class) ||
            !$obj->hasAttribute('attributes') ||
            !\is_a(
                $obj->get('attributes'), 
                \BeAmado\OjsMigrator\MyObject::class
            )
        ) {
            return;
        }

        if ($obj->get('attributes')->hasAttribute($attr))
            return $obj->get('attributes')->get($attr)->getValue();

        return '';
    }

    /**
     * Gets the name of the table to be defined
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return string
     */
    protected function getTableName($obj)
    {
        if ($this->isTable($obj)) 
            return $this->getAttribute($obj, 'name');
    }

    /**
     * Gets the name of the column being defined for the table
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return string
     */
    protected function getColumnName($obj)
    {
        if ($this->isColumn($obj)) 
            return $this->getAttribute($obj, 'name');
    }

    /**
     * Gets the first letter representing the column type as defined in the
     * xml file representing table schema.
     *
     * @param \BeAmado\OjsMigrator\MyObject
     * @return string
     */
    protected function getTypeFirstChar($obj)
    {
        if (
            $this->isColumn($obj) &&
            $this->getAttribute($obj, 'type')
        ) {
            return \substr($this->getAttribute($obj, 'type'), 0, 1);
        }
    }

    /**
     * Gets the last letter representing the column type as defined in the
     * xml file representing table schema.
     *
     * @param \BeAmado\OjsMigrator\MyObject
     * @return string
     */
    protected function getTypeLastChar($obj)
    {
        if (
            $this->isColumn($obj) &&
            $this->getAttribute($obj, 'type')
        ) {
            return \substr($this->getAttribute($obj, 'type'), -1);
        }
    }

    /**
     * Gets the datatype of the column.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return string
     */
    protected function getDataType($obj)
    {
        if (!$this->isColumn($obj)) 
            return;

        switch(\strtoupper($this->getTypeFirstChar($obj))) { 
        //The first character of the type
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

    protected function intTypes()
    {
        return array(
            'tinyint',
            'smallint',
            'int',
            'bigint',
        );
    }

    /**
     * Returns the database integer type which will be the following:
     * 0 - tinyint
     * 1 - smallint
     * 2 - int
     * 3 - bigint
     * 
     * @param integer $number
     * @return string
     */
    protected function getIntType($number)
    {

        if (
            !\is_numeric($number) ||
            $number < 1 ||
            \log($number, 2) >= \count($this->intTypes())
            \fmod(\log($number, 2), 2) != 0 // is not a power of two
        ) {
            return;
        }

        return $this->intTypes()[\log($number, 2)];
    }

    /**
     * Gets the sql type of the column the given obj represents.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return string
     */
    protected function getSqlType($obj)
    {
        if (!$this->isColumn($obj))
            return;

        switch(\strtoupper($this->getTypeFirstChar($obj))) {
            case 'X':
                return 'text';

            case 'I':
                return $this->getIntType($this->getTypeLastChar($obj));

            case 'F':
                return 'double';

            case 'C':
                return $this->getAttribute($obj, 'size') ?
                    'varchar(' . $this->getAttribute($obj, 'size') . ')' :
                    (($this->getTypeLastChar($obj) == 2) ? 'varchar' : 'char');

            case 'D':
                return 'date';

            case 'T':
                return 'datetime';
        }
    }

    /**
     * Receives an object representing a table definition an outputs an array
     * which the \BeAmado\OjsMigrator\Db\Schema class can interpret as defining
     * a database table.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return array
     */
    protected function formatTableDefinitionArray($obj)
    {
        if (!$this->isTable($obj))
            return;

        $def = array();
        $def['name'] = $this->getTableName($obj);

    }

    /**
     * Creates a new \BeAmado\OjsMigrator\db\Schema instance according to the 
     * xml file defining the schema.
     *
     * @param string $filename
     * @return \BeAmado\OjsMigrator\Db\Schema
     */
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
