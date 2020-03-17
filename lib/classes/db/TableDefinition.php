<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\MyObject;
use \BeAmado\OjsMigrator\Registry;
use \BeAmado\OjsMigrator\MyStringRepr;

class TableDefinition extends MyObject implements MyStringRepr
{
    public function __construct($definition = null)
    {
        parent::__construct($definition);

        if (!$this->hasAttribute('columns'))
            return;

        //foreach ($this->get('columns')->toArray() as $name => $def) {
        foreach ($this->getColumnNames() as $name) {
            $this->setColumnDefinition(
                $this->get('columns')->get($name), 
                $name
            );
        }
        unset($name);
    }

    /**
     * Gets the name of the table.
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->get('name')->getValue();
    }

    /**
     * Sets the column definition.
     *
     * @param \BeAmado\OjsMigrator\Db\ColumnDefinition | array $def
     * @param string $name optional
     * @return void
     */
    public function setColumnDefinition($def, $name = null)
    {
        if (\is_array($def))
            $def = new ColumnDefinition($def, $name ?: $def['name']);
        else
            $def = new ColumnDefinition($def, $name);

        if (!$this->hasAttribute('columns'))
            $this->set('columns', array());

        if ($this->get('columns')->hasAttribute($def->getColumnName()))
            $this->get('columns')->remove($def->getColumnName());

        $this->get('columns')->set(
            $name ?: $def->getColumnName(),
            $def
        );
    }

    /**
     * Gets the names of the column being defined
     *
     * @return array
     */
    public function getColumnNames()
    {
        if (!$this->hasAttribute('columns'))
            return;

        //return \array_keys($this->get('columns')->toArray());
        return $this->get('columns')->listKeys();
    }

    /**
     * Checks if the table has the specified column.
     *
     * @param string $colName
     * @return boolean
     */
    public function hasColumn($colName)
    {
        if (!$this->hasAttribute('columns'))
            return false;

        return $this->get('columns')->hasAttribute($colName);
    }

    /**
     * Gets the definition of the specified column.
     *
     * @param string $colName
     * @return \BeAmado\OjsMigrator\Db\ColumnDefinition
     */
    public function getColumn($colName)
    {
        if (!$this->hasColumn($colName)) {
            return;
        }

        return $this->get('columns')->get($colName);
    }

    /**
     * Gets the table primary keys
     *
     * @return array
     */
    public function getPrimaryKeys()
    {
        if ($this->get('primary_keys')->length() > 0)
            return \array_intersect(
                $this->get('primary_keys')->toArray(),
                $this->getColumnNames()
            );

        return array();
    }

    /**
     * Gets the specified column definitions
     *
     * @param array $columnNames
     * @return \BeAmado\OjsMigrator\MyObject
     */
    public function getColumns($columnNames)
    {
        return \array_map(function($column) {
            return $this->hasColumn($column) 
                ? $this->getColumn($column) 
                : null;
        }, $columnNames);
    }

    public function getPrimaryKeyDefinitions()
    {
        return $this->getColumns($this->getPrimaryKeys());
    }

    protected function isColumn($col)
    {
        return \is_a($col, \BeAmado\OjsMigrator\Db\ColumnDefinition::class);
    }

    public function hasAutoIncrement()
    {
        return \array_reduce(
            $this->getPrimaryKeyDefinitions(), 
            function($carry, $column) {
                if (!$this->isColumn($column))
                    return $carry;

                return $carry || $column->isAutoIncrement();
            },
            false
        );
    }

    /**
     * Returns the string representation of the table definition.
     *
     * @return string
     */
    public function toString()
    {
        $repr = '`' . $this->getTableName() . '` (';
        foreach ($this->getColumnNames() as $name) {
            $repr .= $this->getColumn($name)->toString() . ', ';
        }
        unset($name);

        if (!empty($this->getPrimaryKeys())) {
            $repr .= 'PRIMARY KEY(`' 
                . \implode('`, `', $this->getPrimaryKeys())
                . '`)';
        } else if (\substr($repr, -2) === ', ')
            $repr = \substr($repr, 0, -2);

        $repr .= ')';
        return $repr;
    }
}
