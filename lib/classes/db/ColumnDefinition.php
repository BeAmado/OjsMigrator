<?php

namespace BeAmado\OjsMigrator\Db;
use BeAmado\OjsMigrator\MyObject;
use BeAmado\OjsMigrator\MyStringRepr;

class ColumnDefinition extends MyObject implements MyStringRepr
{
    public function __construct($data, $name = null, $isPk = false)
    {
        parent::__construct($data);
        if ($name !== null)
            $this->setColumnName($name);
        else if (\is_array($data) && \array_key_exists('name', $data))
            $this->setColumnName($data['name']);
        else if (
            \is_a($data, \BeAmado\OjsMigrator\MyObject::class) && 
            $data->hasAttribute('name')
        )
            $this->setColumnName($data->get('name')->getValue());


        if ($isPk)
            $this->markAsPrimaryKey();
    }

    public function markAsPrimaryKey()
    {
        $this->set('primary_key', true);
    }

    public function setColumnName($name)
    {
        $this->set('name', $name);
    }

    public function getColumnName()
    {
        if ($this->hasAttribute('name'))
            return $this->get('name')->getValue();
    }

    protected function is($attr)
    {
        if ($this->hasAttribute($attr))
            return (bool) $this->get($attr)->getValue();

        return false;
    }

    public function isPrimaryKey()
    {
        return $this->is('primary_key');
    }

    public function isAutoIncrement()
    {
        return $this->is('auto_increment');
    }

    public function isNullable()
    {
        return ($this->isPrimaryKey() && $this->isAutoIncrement())
            || $this->is('nullable');
    }

    public function getDefaultValue()
    {
        if ($this->hasAttribute('default'))
            return $this->get('default')->getValue();
    }

    public function getDataType()
    {
        if ($this->hasAttribute('type'))
            return $this->get('type')->getValue();
    }

    public function getSqlType()
    {
        if ($this->hasAttribute('sql_type'))
            return $this->get('sql_type')->getValue();
    }

    protected function autoIncrement()
    {
        return 'AUTO_INCREMENT';
    }

    public function toString()
    {
        $repr = '`' . $this->getColumnName() . '` ';
        $repr .= strtoupper($this->getSqlType());
        
        if (!$this->isNullable())
            $repr .= ' NOT NULL';

        if ($this->getDefaultValue() !== null) {
            $repr .= ' DEFAULT ';
            if ($this->getDataType() === 'string' )
                $repr .= '"' . $this->getDefaultValue() . '"';
            else
                $repr .= $this->getDefaultValue();
        }

        if ($this->isAutoIncrement())
            $repr .= ' ' . $this->autoIncrement();

        return $repr;
    }

    public function getSize()
    {
        if (\strtolower($this->getDataType()) !== 'string')
            return;

        $openParens = \strpos($this->getDataType(), '(');
        $closeParens = \strpos($this->getDataType(), ')');

        $size = \substr(
            $this->getDataType(),
            $openParens + 1,
            $closeParens - $openParens
        );

        unset($closeParens);
        unset($openParens);

        return (int) $size;
    }
}
