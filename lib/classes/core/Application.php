<?php

namespace BeAmado\OjsMigrator;

class Application
{
    protected function preload($args)
    {
        Maestro::setOjsDir($args['OjsDir']);
        Maestro::setSchemaDir();
        Registry::get('SchemaHandler')->loadAllSchema();
    }

    protected function finish()
    {
        Registry::get('SchemaHandler')->removeSchemaDir();
        Registry::clear();
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
        }

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
        exit($signal);
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
        foreach ($this->getEntityFilesToImport($tableName) as $filename)
        {
            $this->getHandler($tableName)->import(
                Registry::get('JsonHandler')->createFromFile($filename)
            );
        }
    }

    protected function importEntities($entities)
    {
        foreach ($this->entitiesOrder() as $tableName) {
            if (\in_array($tableName, $entities))
                $this->importEntity($tableName);
        }
    }

    protected function runImport()
    {
        // decompress the entities tar.gz file

        // choose the journal to import the entities
        $journal = Registry::get('MigrationManager')->chooseJournal();

        // map the journal

        // import the entities
    }

    protected function runExport()
    {
        // choose the journal to export
        $journal = Registry::get('MigrationManager')->chooseJournal();

        // export the entities

        // compress the entities dir into a tar.gz file
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
        } finally {
            $lastError = \error_get_last();
            if ($lastError)
                var_dump($lastError);

            $this->endFlow();
        }
    }

}
