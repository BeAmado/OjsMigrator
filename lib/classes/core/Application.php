<?php

namespace BeAmado\OjsMigrator;

///////////// Managers ////////////////////////
use \BeAmado\OjsMigrator\Util\FileSystemManager;
use \BeAmado\OjsMigrator\Util\MemoryManager;
use \BeAmado\OjsMigrator\Util\ArchiveManager;
use \BeAmado\OjsMigrator\Util\IoManager;

///////////// Handlers ////////////////////////
use \BeAmado\OjsMigrator\Util\ConfigHandler;
use \BeAmado\OjsMigrator\Util\FileHandler;
use \BeAmado\OjsMigrator\Util\XmlHandler;
use \BeAmado\OjsMigrator\Util\JsonHandler;
use \BeAmado\OjsMigrator\Db\SchemaHandler;
use \BeAmado\OjsMigrator\Db\DbHandler;

class Application
{
    protected function loadManagers()
    {
        Registry::set('FileSystemManager', new FileSystemManager());
        Registry::set('MemoryManager', new MemoryManager());
        Registry::set('ArchiveManager', new ArchiveManager());
        Registry::set('IoManager', new IoManager());
    }

    protected function loadHandlers()
    {
        if (!Registry::hasKey('ConfigHandler'))
            Registry::set('ConfigHandler', new ConfigHandler());

        Registry::set('FileHandler', new FileHandler());
        Registry::set('DbHandler', new DbHandler());
        Registry::set('XmlHandler', new XmlHandler());
        Registry::set('SchemaHandler', new SchemaHandler());
        Registry::set('JsonHandler', new JsonHandler());
    }


    protected function setOjsDir($dir = null)
    {
        if (!Registry::hasKey('FileSystemManager'))
            Registry::set('FileSystemManager', new FileSystemManager());

            
    }

    /**
     * Loads the Handlers and Managers
     */
    protected function preload($args)
    {
        $this->loadManagers();
        $this->loadHandlers();

        $this->setOjsDir($args['OjsDir']);

        $this->setSchemaDir();
        $this->loadSchema();
    }

    protected function finish()
    {
        $this->removeSchema();
        Registry::clear();
    }

    public function run($ojsDir = null)
    {
        $this->preload(array(
            'OjsDir' => $ojsDir,
        ));
        Registry::get('IoManager')->writeToStdout(
            '############### OJS journal migration #################' . PHP_EOL
        );
        $this->finish();
    }

}
