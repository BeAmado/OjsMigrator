<?php

namespace BeAmado\OjsMigrator;

class Factory
{
    ///////////////////////////// HANDLERS ///////////////////////////////////
    
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
        if (\method_exists($this, 'create' . $classname))
            return $this->{'create' . $classname}($args);
    }
}
