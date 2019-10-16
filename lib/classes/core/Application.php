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
        if (!Registry::hasKey('ConfigHandler')) {
            Registry::set('ConfigHandler', new ConfigHandler());
        }

        Registry::set('FileHandler', new FileHandler());
        Registry::set('DbHandler', new DbHandler());
        Registry::set('XmlHandler', new XmlHandler());
        Registry::set('SchemaHandler', new SchemaHandler());
    }

    protected function setSchemaDir()
    {
        Registry::set(
            'SchemaDir',
            BASE_DIR . DIR_SEPARATOR . 'schema'
        );
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

        $vars->set(
            'xmlContent',
            Registry::get('XmlHandler')->createFromFile(
                $vars->get('schemaLocationsFile')->getValue()
            )
        );

        $vars->get('xmlContent')->get('children')->forEachValue(function($o) {
            if (\strtolower($o->get('name')->getValue()) !== 'schema')
                return;

            Registry::remove('schema');
            //var_dump(Registry::get('OjsDir'));
            Registry::set(
                'schema',
                Registry::get('SchemaHandler')->createFromFile(
                    Registry::get('FileSystemManager')->formPath(
                        \array_merge(
                            \explode(
                                DIR_SEPARATOR,
                                Registry::get('OjsDir')
                            ),
                            \explode(
                                '/', 
                                $o->get('attributes')->get('file')->getValue()
                            )
                        )
                    )
                )
            );

            Registry::get('SchemaHandler')->saveSchema(Registry::get('schema'));
        });

        Registry::remove('schema');
        Registry::get('MemoryManager')->destroy($vars);
        unset($vars);
    }

    protected function removeSchema()
    {
        Registry::get('FileSystemManager')->removeWholeDir(
            Registry::get('SchemaDir')
        );
    }

    protected function setOjsDir($test = true)
    {
        if ($test) {
            Registry::get('ArchiveManager')->tar(
                'xzf',
                Registry::get('FileSystemManager')->formPathFromBaseDir(array(
                    'tests',
                    '_data',
                    'ojs2.tar.gz',
                )),
                BASE_DIR . DIR_SEPARATOR . 'sandbox'
            );

            Registry::set(
                'OjsDir',
                Registry::get('FileSystemManager')->formPathFromBaseDir(array(
                    'sandbox',
                    'ojs2',
                    'public_html',
                ))
            );

            return;
        }
            
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
