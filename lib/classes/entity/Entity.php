<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class Entity extends \BeAmado\OjsMigrator\MyObject
{
    public function __construct($data, $tableName = null)
    {
        parent::__construct($data);
        if (\is_string($tableName))
            $this->setTableName($tableName);
    }

    protected function setTableName($name)
    {
        if (\is_string($name))
            $this->set('__tableName_', $name);
    }

    public function getData($attr)
    {
        if (!$this->hasAttribute($attr))
            return;

        if (\count($this->get($attr)->listKeys()) === 0)
            return $this->get($attr)->getValue();

        $data = array();
        foreach ($this->get($attr)->listKeys() as $key) {
            $data[$key] = $this->get($attr)->get($key);
        }

        return $data;
    }

    public function getTableName()
    {
        if ($this->hasAttribute('__tableName_'))
            return $this->get('__tableName_')->getValue();
    }


    public function getId()
    {
        return $this->getData(
            Registry::get('EntityHandler')->getIdField($this->getTableName())
        );
    }

    public function setId($id)
    {
        return $this->set(
            Registry::get('EntityHandler')->getIdField($this->getTableName()),
            $id
        );
    }
}
