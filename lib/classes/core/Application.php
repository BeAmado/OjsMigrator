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
        if (!Registry::hasKey('ConfigHandler')) {
            Registry::set('ConfigHandler', new ConfigHandler());
        }

        Registry::set('FileHandler', new FileHandler());
        Registry::set('DbHandler', new DbHandler());
    }

    /**
     * Loads the Handlers and Managers
     */
    protected function preload()
    {
        $this->loadManagers();
        $this->loadHandlers();
    }

    public function run()
    {
        $this->preload();
        Registry::get('IoManager')->writeToStdout(
            '############### OJS journal migration #################' . PHP_EOL
        );
    }
}
