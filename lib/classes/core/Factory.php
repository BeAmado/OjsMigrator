<?php

namespace BeAmado\OjsMigrator;

class Factory
{
    ///////////////////////////// HANDLERS ///////////////////////////////////
    protected function createArrayHandler()
    {
        return new \BeAmado\OjsMigrator\Util\ArrayHandler();
    }

    protected function createCaseHandler()
    {
        return new \BeAmado\OjsMigrator\Util\CaseHandler();
    }

    protected function createColumnDefinitionHandler()
    {
        return new \BeAmado\OjsMigrator\Db\ColumnDefinitionHandler();
    }
    
    protected function createConfigHandler($configFile = null)
    {
        return new \BeAmado\OjsMigrator\Util\ConfigHandler($configFile);
    }

    protected function createDbHandler()
    {
        return new \BeAmado\OjsMigrator\Db\DbHandler();
    }

    protected function createFileHandler()
    {
        return new \BeAmado\OjsMigrator\Util\FileHandler();
    }

    protected function createIndexDefinitionHandler()
    {
        return new \BeAmado\OjsMigrator\Db\IndexDefinitionHandler();
    }

    protected function createJsonHandler()
    {
        return new \BeAmado\OjsMigrator\Util\JsonHandler();
    }

    protected function createQueryHandler()
    {
        return new \BeAmado\OjsMigrator\Db\QueryHandler();
    }

    protected function createSchemaHandler()
    {
        return new \BeAmado\OjsMigrator\Db\SchemaHandler();
    }

    protected function createTableDefinitionHandler()
    {
        return new \BeAmado\OjsMigrator\Db\TableDefinitionHandler();
    }

    protected function createXmlHandler()
    {
        return new \BeAmado\OjsMigrator\Util\XmlHandler();
    }

    protected function createZipHandler()
    {
        return new \BeAmado\OjsMigrator\Util\ZipHandler();
    }


    ////////////////////////////// MANAGERS //////////////////////////////////

    protected function createArchiveManager()
    {
        return new \BeAmado\OjsMigrator\Util\ArchiveManager();
    }

    protected function createConnectionManager()
    {
        return new \BeAmado\OjsMigrator\Db\ConnectionManager();
    }

    protected function createFileSystemManager()
    {
        return new \BeAmado\OjsMigrator\Util\FileSystemManager();
    }

    protected function createMemoryManager()
    {
        return new \BeAmado\OjsMigrator\Util\MemoryManager();
    }

    protected function fixCase($classname)
    {
        switch (\strtolower($classname)) {
            case \strtolower('ConfigHandler'):
                return 'ConfigHandler';
            case \strtolower('DbHandler'):
                return 'DbHandler';
            case \strtolower('FileHandler'):
                return 'FileHandler';
            case \strtolower('JsonHandler'):
                return 'JsonHandler';
            case \strtolower('QueryHandler'):
                return 'QueryHandler';
            case \strtolower('SchemaHandler'):
                return 'SchemaHandler';
            case \strtolower('XmlHandler'):
                return 'XmlHandler';
            case \strtolower('ZipHandler'):
                return 'ZipHandler';
            case \strtolower('ArchiveManager'):
                return 'ArchiveManager';
            case \strtolower('ConnectionManager'):
                return 'ConnectionManager';
            case \strtolower('FileSystemManager'):
                return 'FileSystemManager';
            case \strtolower('MemoryManager'):
                return 'MemoryManager';
        }

        return $classname;
    }

    /**
     * Creates an instance of the specified class passing the parameters to
     * the class constructor.
     *
     * @param string $classname
     * @param array $args
     * @return mixed
     */
    public function create($classname, $args = null)
    {
        if (\method_exists($this, 'create' . $this->fixCase($classname)))
            return $this->{'create' . $this->fixCase($classname)}($args);
    }
}
