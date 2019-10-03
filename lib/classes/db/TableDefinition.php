<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\MyObject;
use \BeAmado\OjsMigrator\Util\MemoryManager;

class TableDefinition extends MyObject
{
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

    public function isNullable($colName)
    {
        return !$this->isPrimaryKey($colName) && 
            $this->is('nullable', $colName);
    }

    public function isPrimaryKey($colName)
    {
        return $this->is('primary_key', $colName);
    }

    public function defaultValue($colName)
    {
        return $this->getColumnAttribute($colName, 'default');
    }

    public function getColumnType($colName)
    {
        return $this->getColumnAttr($colName, 'type');
    }

    public function getSqlType($colName)
    {
        return $this->getColumnAttr($colName, 'sql_type');
    }

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
            $vars->get('closeParens')->getValue() - $vars->get('openParens')->getValue()
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
