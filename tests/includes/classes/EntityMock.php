<?php

namespace BeAmado\OjsMigrator;

abstract class EntityMock
{
    /**
     * @var string
     */
    protected $tableName;

    public function __construct($name)
    {
        $this->setTableName($name);
    }

    protected function removeBrackets($str)
    {
        if (
            \substr($str, 0, 1) === '[' &&
            \substr($str, -1) === ']'
        )
            return \substr($str, 1, -1);

        return $str;
    }

    protected function setTableName($name)
    {
        if (\is_string($name))
            $this->tableName = $name;
    }

    protected function getTableName()
    {
        if (!isset($this->tableName) || !\is_string($this->tableName))
            return;

        return $this->tableName;
    }

    protected function getMockedEntityDir()
    {
        if (!\is_string($this->getTableName()))
            return;

        return Registry::get('FileSystemManager')->formPathFromBaseDir(array(
            'tests',
            '_data',
            'mocks',
            $this->getTableName(),
        ));
    }

    protected function formFilename($filename)
    {
        return $this->getMockedEntityDir()
            . \BeAmado\OjsMigrator\DIR_SEPARATOR 
            . \strtolower($filename) . '.php';
    }

    protected function get($name)
    {
        if (!Registry::get('FileSystemManager')->fileExists(
            $this->formFilename($name)
        ))
            return;

        return Registry::get('MemoryManager')->create(
            include($this->formFilename($name))
        );
    }
}
