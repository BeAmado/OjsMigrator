<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\MyObject;
use \BeAmado\OjsMigrator\Util\MemoryManager;

class TableDefinition extends MyObject
{
    /**
     * Gets the name of the table.
     *
     * @return string
     */
    public function getName()
    {
        return $this->get('name')->getValue();
    }

    /**
     * Checks if the table has the specified column.
     *
     * @param string $colName
     * @return boolean
     */
    public function hasColumn($colName)
    {
        if (!$this->hasAttribute('columns')) {
            return false;
        }

        return $this->get('columns')->hasAttribute($colName);
    }

    protected function getColumn($colName)
    {
        if (!$this->hasColumn($colName)) {
            return;
        }

        return $this->get('columns')->get($colName);
    }

    protected function getColumnAttribute($colName, $attr)
    {
        if (
            !$this->hasColumn($colName) ||
            !$this->getColumn($colName)->hasAttribute($attr)
        ) {
            return;
        }

        return $this->getColumn($colName)->get($attr)->getValue();
    }

    protected function is($attr, $colName)
    {
        return $this->hasColumn($colName) &&
            $this->getColumn($colName)->hasAttribute($attr) &&
            $this->getColumn($colName)->get($attr)->getValue();
    }

    /**
     * Checks if the specified column might be null.
     * 
     * @param string $colName
     * @return boolean
     */
    public function isNullable($colName)
    {
        return !$this->isPrimaryKey($colName) && 
            $this->is('nullable', $colName);
    }

    /**
     * Checks if the specified column is a primary key.
     *
     * @param string $colName
     * @return boolean
     */
    public function isPrimaryKey($colName)
    {
        return $this->is('primary_key', $colName);
    }

    /**
     * Gets the default value of the specified column.
     *
     * @param string $colName
     * @return mixed
     */
    public function getDefaultValue($colName)
    {
        return $this->getColumnAttribute($colName, 'default');
    }

    /**
     * Gets the PHP data type of the specified column.
     *
     * @param string $colName
     * @return string
     */
    public function getDataType($colName)
    {
        return $this->getColumnAttr($colName, 'type');
    }

    /**
     * Gets the SQL type of the specified column.
     *
     * @param string $colName
     * @return string
     */
    public function getSqlType($colName)
    {
        return $this->getColumnAttr($colName, 'sql_type');
    }

    /**
     * Gets the maximum size of the varchar column.
     *
     * @param string $colName
     * @return integer
     */
    public function getSize($colName)
    {
        if ($this->getColumnType($colName) !== 'string') {
            return;
        }

        if (
            \strpos($this->getSqlType($colName), 'varchar') === false &&
            \strpos($this->getSqlType($colName), 'char') !== false
        ) {
            return 1;
        }

        $vars = (new MemoryManager())->create(array(
            'openParens' => \strpos($this->getSqlType($colName), '('),
            'closeParens' => \strpos($this->getSqlType($colName), ')'),
        ));

        $vars->set(
            'length',
            $vars->get('closeParens')->getValue() 
                - $vars->get('openParens')->getValue()
        );

        $size = (int) \substr(
            $this->getSqlType($colName),
            $vars->get('openParens')->getValue() + 1,
            $vars->get('length')->getValue()
        );

        (new MemoryManager())->destroy($vars);
        unset($vars);

        return $size;
    }
}
