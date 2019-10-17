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

    protected function setSchemaDir()
    {
        Registry::set(
            'SchemaDir',
            BASE_DIR . DIR_SEPARATOR . 'schema'
        );
    }

    /**
     * 
     * 
     * @param \BeAmado\OjsMigrator\MyObject $xml
     * @return string
     */
    protected function getSchemaFile($xml)
    {
        if (\strtolower($xml->get('name')->getValue()) !== 'schema')
            return;

        return Registry::get('FileSystemManager')->formPath(
            \array_merge(
                \explode(
                    DIR_SEPARATOR,
                    Registry::get('OjsDir')
                ),
                \explode(
                    '/', 
                    $xml->get('attributes')->get('file')->getValue()
                )
            )
        );
    }

    /**
     * 
     * 
     * @param \BeAmado\OjsMigrator\MyObject $xml
     * @return \BeAmado\OjsMigrator\Db\Schema
     */
    protected function getSchema($xml)
    {
        return Registry::get('SchemaHandler')->createFromFile(
            $this->getSchemaFile($xml)
        );
    }

    /**
     * Saves the table definitions from the schema that are in the files 
     * indicated in the xml.
     *
     * @param \BeAmado\OjsMigrator\MyObject $xml
     * @return void
     */
    protected function saveDefinitionsFromSchema($xml)
    {
        $xml->get('children')->forEachValue(function($o) {
            if (\strtolower($o->get('name')->getValue()) !== 'schema')
                return;

            Registry::get('SchemaHandler')->saveSchema($this->getSchema($o));
        });
    }

    protected function loadSchema()
    {
        if (!Registry::hasKey('SchemaDir'))
            $this->setSchemaDir();

        if (!Registry::get('FileSystemManager')->dirExists(
            Registry::get('SchemaDir')
        ))
            Registry::get('FileSystemManager')->createDir(
                Registry::get('SchemaDir')
            );

        $vars = Registry::get('MemoryManager')->create();

        // setting the schema locations file to be 
        // [ojs_dir]/dbscripts/xml/install.xml
        $vars->set(
            'schemaLocationsFile',
            Registry::get('FileSystemManager')->formPath(\array_merge(
                \explode(DIR_SEPARATOR, Registry::get('OjsDir')),
                array(
                    'dbscripts',
                    'xml',
                    'install.xml',
                )
            ))
        );

        // xmlContent will be the data in the file dbscripts/xml/install.xml
        $vars->set(
            'xmlContent',
            Registry::get('XmlHandler')->createFromFile(
                $vars->get('schemaLocationsFile')->getValue()
            )
        );

        $this->saveDefinitionsFromSchema($vars->get('xmlContent'));

        Registry::get('MemoryManager')->destroy($vars);
        unset($vars);
    }

    protected function removeSchema()
    {
        Registry::get('FileSystemManager')->removeWholeDir(
            Registry::get('SchemaDir')
        );
    }

    protected function setOjsDir($dir = null)
    {
        if (!Registry::hasKey('FileSystemManager'))
            Registry::set('FileSystemManager', new FileSystemManager());

        Registry::set(
            'OjsDir',
            $dir ?: Registry::get('FileSystemManager')->parentDir(BASE_DIR)
        );
            
    }

    /**
     * Loads the Handlers and Managers
     */
    protected function preload()
    {
        $this->loadManagers();
        $this->loadHandlers();

        $this->setOjsDir(true);

        $this->setSchemaDir();
        $this->loadSchema();
    }

    protected function finish()
    {
        $this->removeSchema();
        Registry::clear();
    }

    public function run()
    {
        $this->preload();
        Registry::get('IoManager')->writeToStdout(
            '############### OJS journal migration #################' . PHP_EOL
        );
        $this->finish();
    }

}
