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

        foreach ($this->get('columns')->toArray() as $name => $def) {
            $this->setColumnDefinition($def, $name);
        }
        unset($name);
        unset($def);
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

        return \array_keys($this->get('columns')->toArray());
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
     * Gets the definiction of the specified column.
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
        return $this->get('primary_keys')->toArray();
    }

    public function toString()
    {
        $repr = '`' . $this->getTableName() . '` (';
        foreach ($this->getColumnNames() as $name) {
            $repr .= $this->getColumn($name)->toString() . ', ';
        }
        unset($name);

        if (\count($this->getPrimaryKeys())) {
            $repr .= 'PRIMARY KEY(`' 
                . \implode('`, `', $this->getPrimaryKeys())
                . '`)';
        } else if (\substr($repr, -2) === ', ')
            $repr = \substr($repr, 0, -2);

        $repr .= ')';
        return $repr;
    }
}
