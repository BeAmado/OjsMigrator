<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\Registry;

class TableDefinitionHandler extends AbstractDefinitionHandler
{
    protected function getUniqueColumns($obj)
    {
        $uniques = array();
        $indexes = $this->getIndexesRaw($obj);
        $idefHandler = Registry::get('IndexDefinitionHandler');
        foreach ($indexes as $index) {
            if ($idefHandler->isUniqueColumnIndex($index))
                $uniques[] = $idefHandler->getIndexColumns[0];
        }
        unset($index);
        unset($idefHandler);

        return $uniques;

    }

    /**
     * Gets the columns in the raw table definition.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return array
     */
    public function getColumns($obj)
    {
        $defs = array();
        $cdHandler = Registry::get('ColumnDefinitionHandler');
        $uniques = $this->getUniqueColumns($obj);

        /** @var $column \BeAmado\OjsMigrator\MyObject */
        foreach (
            Registry::get('TableDefinitionHandler')->getColumnsRaw($obj) 
            as $column
        ) {
            $def = array(
                'name' => $cdHandler->getColumnName($column),
                'type' => $cdHandler->getDataType($column),
                'sql_type' => $cdHandler->getSqlType($column),
                'nullable' => !$this->hasChild($column,'notnull'),
            );

            if ($this->hasChild($column, 'key'))
                $def['primary_key'] = true;

            if ($this->hasChild($column, 'autoincrement'))
                $def['auto_increment'] = true;

            if ($this->hasChild($column, 'default'))
                $def['default'] = $cdHandler->getDefaultValue($column);

            if (\in_array($cdHandler->getColumnName($column), $uniques))
                $def['unique'] = true;

            $defs[$cdHandler->getColumnName($column)] = $def;

            Registry::get('MemoryManager')->destroy($def);
            unset($def);
        }
        unset($cdHandler);

        return $defs;
    }

    /**
     * Returns an array with the names of the columns that are primary keys.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @param array $columns
     * @return array
     */
    public function getPrimaryKeys($obj, $columns = null)
    {
        if (!\is_array($columns))
            $columns = $this->getColumns($obj);

        $pks = array();

        foreach ($columns as $column) {
            if (
                \is_array($column) && 
                \array_key_exists('primary_key', $column)
            )
                $pks[] = $column['name'];
        }

        foreach (
            Registry::get('MemoryManager')->create($this->getIndexesRaw($obj)) 
            as $index
        ) {
            if (Registry::get('IndexDefinitionHandler')->isIndexPk($index)) {
                $pks = \array_merge(
                    $pks,
                    Registry::get('IndexDefinitionHandler')->getIndexColumns(
                        $index
                    )
                );
            }
        }

        return \array_unique($pks);
    }

    /**
     * Check whether or not the given obj is defining a table.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return boolean
     */
    public function isTable($obj)
    {
        return $this->nameIs($obj, 'table');
    }

    /**
     * Gets the column definitions of the table.
     *
     * @param \BeAmado\OjsMigrator\MyObject $table
     * @return array
     */
    protected function getColumnsRaw($table)
    {
        if (!$this->isTable($table))
            return;

        Registry::remove('columns');
        Registry::set(
            'columns',
            Registry::get('MemoryManager')->create()
        );

        $table->get('children')->forEachValue(function($elem) {
            if (Registry::get('ColumnDefinitionHandler')->isColumn($elem))
                Registry::get('columns')->push($elem);
        });

        return Registry::get('columns')->toArray();
    }

    /**
     * Gets the indexes definitions of the table.
     *
     * @param \BeAmado\OjsMigrator\MyObject $table
     * @return array
     */
    protected function getIndexesRaw($table)
    {
        if (!$this->isTable($table))
            return;
        
        if (\is_array($table))
            return $this->getIndexesRaw(
                Registry::get('MemoryManager')->create($table)
            );

        Registry::remove('indexes');
        Registry::set(
            'indexes',
            Registry::get('MemoryManager')->create()
        );

        $table->get('children')->forEachValue(function($elem) {
            if (Registry::get('IndexDefinitionHandler')->isIndex($elem))
                Registry::get('indexes')->push($elem);
        });

        return Registry::get('indexes')->toArray();
    }

    /**
     * Gets the name of the table to be defined
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return string
     */
    public function getTableName($obj)
    {
        if ($this->isTable($obj)) 
            return $this->getAttribute($obj, 'name');
    }
}
