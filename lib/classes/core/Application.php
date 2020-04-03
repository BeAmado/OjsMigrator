<?php

namespace BeAmado\OjsMigrator;

class Application
{
    /**
     * @var string
     */
    private $status;

    public function __construct()
    {
        $this->status = 'idle';
    }

    public function isRunning()
    {
        return \strtolower($this->status) === 'running';
    }

    protected function start()
    {
        $this->status = 'running';
    }

    protected function stop()
    {
        $this->status = 'stopped';
    }

    protected function preload($args)
    {
        $this->start();
        Maestro::setOjsDir($args['OjsDir']);
        Maestro::setSchemaDir();
        Registry::get('SchemaHandler')->loadAllSchema();
    }

    protected function finish()
    {
        Registry::get('SchemaHandler')->removeSchemaDir();
        Registry::clear();
        $this->stop();
    }

    protected function showWelcomeMessage()
    {
        Registry::get('IoManager')->writeToStdout(
            PHP_EOL
            . '############### OJS journal migration #################' 
            . PHP_EOL
            . PHP_EOL
        );
    }

    protected function showEndMessage()
    {
        Registry::get('IoManager')->writeToStdout(
            PHP_EOL 
            . PHP_EOL 
            . '############# End of program #############' 
            . PHP_EOL
        );
    }

    protected function setMigrationOptions()
    {
        Registry::get('MigrationManager')->setImportExportAction();

        if (\strtolower(
            Registry::get('MigrationManager')->getMigrationOption('action')
        ) === 'exit') {
            $this->endFlow(100);
            return;
        }

        Registry::get('MigrationManager')->chooseJournal();

        Registry::get('MigrationManager')->chooseEntitiesToMigrate();
    }

    protected function getHandler($table)
    {
        if (!\in_array($table, array(
            'announcements',
            'groups',
            'issues',
            'journals',
            'review_forms',
            'sections',
            'submissions',
            'users',
        )))
            return;

        return Registry::get(\implode('', array(
            Registry::get('CaseHandler')->transformCaseTo(
                'Pascal', 
                Registry::get('GrammarHandler')->getSingle($table)
            ),
            'Handler'
        )));
    }


    protected function beginFlow()
    {
        $this->showWelcomeMessage();
        $this->setMigrationOptions();
    }

    protected function endFlow($signal = 0)
    {
        $this->finish();
        $this->showEndMessage();
//        exit($signal);
    }

    protected function entitiesOrder()
    {
        return array(
            1 => 'journals',
            2 => 'users',
            3 => 'announcements',
            4 => 'groups',
            5 => 'review_forms',
            6 => 'sections',
            7 => 'issues',
            8 => 'submissions',
        );
    }

    protected function exportEntities($entities, $journal)
    {
        foreach($this->entitiesOrder() as $tableName) {
            if (\in_array($tableName, $entities))
                $this->getHandler($tableName)->export($journal);
        }
    }

    protected function listEntityDataDir($tableName)
    {
        return Registry::get('FileSystemManager')->listdir(
            Registry::get('EntityHandler')->getEntityDataDir($tableName)
        );
    }

    protected function getEntityFilesToImport($tableName)
    {
        if (in_array($tableName, array(
            'issues',
            'submissions',
        )))
            return \array_map(function($dir) {
                return Registry::get('FileSystemManager')->formPath(array(
                    $dir,
                    \basename($dir) . '.json',
                ));
            }, $this->listEntityDataDir($tableName));

        return $this->listEntityDataDir($tableName);
    }

    protected function importEntity($tableName)
    {
        echo "\n\n\nImporting the '$tableName'...\n\n\n";
        foreach ($this->getEntityFilesToImport($tableName) as $filename)
        {
            $this->getHandler($tableName)->import(
                Registry::get('JsonHandler')->createFromFile($filename)
            );
        }
    }

    protected function importEntities($entities)
    {
        if (!\in_array('journals', $entities))
            $entities[] = 'journals';

        foreach ($this->entitiesOrder() as $tableName) {
            if (\in_array($tableName, $entities))
                $this->importEntity($tableName);
        }
    }

    protected function runImport()
    {
        echo "\n\n\nChose to IMPORT the following entities: ";
        var_dump(Registry::get('MigrationManager')->getEntitiesToMigrate());
        echo "\nAnd the chosen journal: ";
        var_dump(Registry::get('MigrationManager')->getChosenJournal());
        echo "\n\n\n";

        Registry::get('DataMappingManager')->setDataMappingDir(
            Registry::get('MigrationManager')->getChosenJournal()
        );

        var_dump(Registry::get('DataMappingManager')->getDataMappingDir());
        // import the entities
//        $this->importEntities(
//            Registry::get('MigrationManager')->getEntitiesToMigrate()
//                                             ->toArray()
//        );
    }

    protected function runExport()
    {
//        echo "\n\n\nChose to EXPORT the following entities: ";
//        var_dump(Registry::get('MigrationManager')->getEntitiesToMigrate());
//        echo "\nAnd the chosen journal: ";
//        var_dump(Registry::get('MigrationManager')->getChosenJournal());
//        echo "\n\n\n";
        // export the entities

        foreach (Registry::get(
            'MigrationManager'
        )->getEntitiesToMigrate()->toArray() as $table) {
            echo "\n\nExporting the '$table'...\n\n";
            $this->getHandler($table)->export(
                Registry::get('MigrationManager')->getChosenJournal()
            );
        }

        // compress the entities dir into a tar.gz file
        Registry::get('ArchiveManager')->tar(
            'cz',
            Registry::get('FileSystemManager')->formPathFromBaseDir('data'),
            Registry::get('entitiesDir')
        );
    }

    public function run($ojsDir = null)
    {
        try {
            $this->preload(array(
                'OjsDir' => $ojsDir,
            ));

            $this->beginFlow();

            if (Registry::get('MigrationManager')->choseExport())
                $this->runExport();
            else if (Registry::get('MigrationManager')->choseImport())
                $this->runImport();
        } catch (\Exception $e) {
            echo "\n\nCaught the exception:\n'" . $e->getMessage() . "\n\n";
        } catch (\Error $e) {
            echo "\n\nCaught the error: \n'" . $e->getMessage() 
                . "\n" . $e->getTraceAsString() . "\n\n";
                var_dump($e);
        } finally {
            $lastError = \error_get_last();
            if ($lastError)
                var_dump($lastError);

            if ($this->isRunning())
                $this->endFlow();
        }
    }

}
