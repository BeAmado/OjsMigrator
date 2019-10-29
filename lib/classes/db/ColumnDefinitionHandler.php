<?php

namespace BeAmado\OjsMigrator\Db;

use \BeAmado\OjsMigrator\Registry;

class ColumnDefinitionHandler extends AbstractDefinitionHandler
{
    /**
     * Checks whether or not the given object is defining a table column.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return boolean
     */
    public function isColumn($obj)
    {
        return $this->nameIs($obj, 'field');
    }

    /**
     * Gets the name of the column being defined for the table
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return string
     */
    public function getColumnName($obj)
    {
        if ($this->isColumn($obj)) 
            return $this->getAttribute($obj, 'name');
    }

    /**
     * Checks whether or not the given column is defined as a primary key.
     *
     * @param \BeAmado\OjsMigrator\MyObject | array $obj
     * @return boolean
     */
    public function isPkColumn($obj)
    {
        if (!$this->isColumn($obj))
            return;

        if (\is_array($obj))
            return $this->isPkColumn(
                Registry::get('MemoryManager')->create($obj)
            );

        Registry::remove('isPk');
        Registry::set('isPk', false);

        /** @var $attr \BeAmado\OjsMigrator\MyObject */
        $obj->get('children')->forEachValue(function($attr) {
            if ($this->nameIs($attr, 'key'))
                Registry::set('isPk', true);
        });

        return Registry::get('isPk');
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
    public function getDataType($obj)
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
            \log($number, 2) >= \count($this->intTypes()) ||
            \fmod(\log($number, 2), 1) != 0 // is not a power of two
        ) {
            return;
        }

        return $this->intTypes()[(int) \log($number, 2)];
    }

    /**
     * Gets the sql type of the column the given obj represents.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return string
     */
    public function getSqlType($obj)
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
     * Gets the default value of the given column.
     *
     * @param \BeAmado\OjsMigrator\MyObject $column
     * @return mixed
     */
    public function getDefaultValue($column)
    {
        if (
            !$this->isColumn($column) || 
            !$this->hasChild($column, 'default')
        ) {
            return;
        }

        if (\is_array($column))
            return $this->getDefaultValue(
                Registry::get('MemoryManager')->create($column)
            );

        Registry::remove('default');
        Registry::set('default', null);

        $column->get('children')->forEachValue(function($child) {
            if ($this->nameIs($child, 'default'))
                Registry::set(
                    'default', 
                    $this->getAttribute($child, 'VALUE')
                );
        });

        return Registry::get('default');
    }
}
