<?php

namespace BeAmado\OjsMigrator\Test;
use \BeAmado\OjsMigrator\Registry;

class OjsScenarioHandler
{
    protected function sep()
    {
        return \BeAmado\OjsMigrator\DIR_SEPARATOR;
    }

    public function getOjsDir()
    {
        return $this->getSandboxDir() . $this->sep() . 'ojs2'; 
    }

    public function getOjsPublicHtmlDir()
    {
        return $this->getOjsDir() . $this->sep() . 'public_html';
    }

    public function getOjsFilesDir()
    {
        return $this->getOjsDir() . $this->sep() . 'files';
    }

    public function getOjsConfigFile()
    {
        return $this->getOjsPublicHtmlDir() . $this->sep() . 'config.inc.php';
    }

    public function getOjsConfigTemplateFile()
    {
        return $this->getOjsPublicHtmlDir()
            . $this->sep() . 'config.TEMPLATE.inc.php';
    }

    public function getSandboxDir()
    {
        return Registry::get('FileSystemManager')->formPathFromBaseDir(
            array(
                'tests',
                '_data',
                'sandbox',
            )
        );
    }

    public function createSandbox()
    {
        $sandbox = $this->getSandboxDir();
        
        if (!Registry::get('FileSystemManager')->dirExists($sandbox))
            Registry::get('FileSystemManager')->createDir($sandbox);

        unset($sandbox);
    }

    public function untarOjsDir()
    {
        $ojsTar = Registry::get('FileSystemManager')->formPathFromBaseDir(
            array(
                'tests',
                '_data',
                'ojs2.tar.gz',
            )
        );

        Registry::get('Archivemanager')->tar(
            'xzf',
            $ojsTar,
            $this->getSandboxDir()
        );
    }

    public function ojsDirExists()
    {
        return Registry::get('FileSystemManager')->dirExists(
            $this->getOjsPublicHtmlDir()
        );
    }

    public function setEntitiesDir()
    {
        Registry::set(
            'EntitiesDir',
            $this->getSandboxDir() 
                . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'entities'
        );
    }

    public function createTables($tables = array())
    {
        if (!Registry::hasKey('createdTables'))
            Registry::set(
                'createdTables', 
                Registry::get('MemoryManager')->create()
            );

        foreach ($tables as $table) {
            if (\strpos($table, 'submission') !== false)
                $table = Registry::get('SubmissionHandler')
                                 ->formTableName($table);

            if (Registry::get('DbHandler')->createTableIfNotExists($table))
                Registry::get('createdTables')->push($table);
        }
    }

    protected function createConfigPreprocessor($args = array())
    {
        if ($args === null)
            $args = array();

        $dbDriverFilename = Registry::get('FileSystemManager')
                                    ->formPathFromBaseDir(array(
            'tests',
            'dbdriver',
        ));

        if (
            !\array_key_exists('dbDriver', $args) &&
            Registry::get('FileSystemManager')->fileExists($dbDriverFilename)
        )
            $args['dbDriver'] = Registry::get('FileHandler')->read(
                $dbDriverFilename
            );

        if (!Registry::hasKey('ConfigPreprocessor'))
            Registry::set('ConfigPreprocessor', new ConfigPreprocessor($args));
    }

    public function setUpStage($args = array())
    {
        if (!Registry::hasKey('OjsDir'))
            Registry::set('OjsDir', $this->getOjsPublicHtmlDir());

        $this->createConfigPreprocessor($args);

        if (!Registry::hasKey('EntitiesDir'))
            $this->setEntitiesDir();

        if (!$this->ojsDirExists()) {
            $this->createSandbox();
            $this->untarOjsDir();
            Registry::get('ConfigPreprocessor')->createConfigFile();
        }

        if (\array_key_exists('createTables', $args))
            $this->createTables($args['createTables']);
        
        (new DataMappingHandler())->setUpDataMappingStage();
    }

    public function removeSandbox()
    {
        Registry::get('FileSystemManager')->removeWholeDir(
            $this->getSandboxDir()
        );
    }

    public function dropCreatedTables()
    {
        if (!Registry::hasKey('createdTables'))
            return;

        Registry::get('createdTables')->forEachValue(function($table) {
            Registry::get('DbHandler')->dropTableIfExists($table->getValue());
        });

        Registry::remove('createdTables');
    }

    public function tearDownStage($args = array())
    {
        $this->dropCreatedTables();
        $this->removeSandbox();
        Registry::get('SchemaHandler')->removeSchemaDir();
        (new DataMappingHandler())->tearDownDataMappingStage();
    }
    
    public function getOjs2XmlSchemaFilename()
    {
        return Registry::get('FileSystemManager')->formPath(array(
            $this->getOjsPublicHtmlDir(),
            'dbscripts',
            'xml',
            'ojs_schema.xml',
        ));
    }
}
