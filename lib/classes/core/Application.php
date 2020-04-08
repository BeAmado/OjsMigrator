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
            '############### OJS journal migration #################',
            false, // do not clear the stdout
            1, // one line break before
            2 // two line breaks after
        );
    }

    protected function showEndMessage()
    {
        Registry::get('IoManager')->writeToStdout(
            '############# End of program #############' ,
            false, // do not clear the stdout
            2, // two line breaks before
            1 // one line break after
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
        if ($tableName === 'submissions')
            $tableName = Registry::get('SubmissionHandler')->formTableName();

        if (in_array($tableName, array(
            'issues',
            'submissions', 'articles',
        )))
            return \array_map(function($dir) {
                return Registry::get('FileSystemManager')->formPath(array(
                    $dir,
                    \basename($dir) . '.json',
                ));
            }, $this->listEntityDataDir($tableName));

        return $this->listEntityDataDir($tableName);
    }

    protected function reportTableImportation($table)
    {
        Registry::get('IoManager')->writeToStdout(
            implode('', array(
                'Importing the "', $table, '"...',
            )),
            false,
            2,
            1
        );
    }

    protected function importJournal()
    {
        return $this->getHandler('journals')->import(
            Registry::get('JsonHandler')->createFromFile(
                $this->getHandler('journals')
                     ->getJournalFilenameInEntitiesDir()
            )
        );
    }

    protected function importEntity($tableName)
    {
        $this->reportTableImportation($tableName);

        if ($tableName === 'journals')
            return $this->importJournal();

        foreach ($this->getEntityFilesToImport($tableName) as $filename)
        {
            $this->getHandler($tableName)->import(
                Registry::get('JsonHandler')->createFromFile($filename)
            );
        }
    }

    protected function importEntities($entities)
    {
        if (!\is_array($entities))
            return;

        if (!\in_array('journals', $entities))
            $entities[] = 'journals';

        foreach ($this->entitiesOrder() as $tableName) {
            if (\in_array($tableName, $entities))
                $this->importEntity($tableName);
        }
    }

    protected function mapJournal($journalId)
    {
        if (!Registry::get('DataMapper')->isMapped(
            'journals',
            $journalId
        ))
            Registry::get('DataMapper')->mapData(
                'journals',
                array(
                    'old' => $journalId,
                    'new' => Registry::get('MigrationManager')
                                     ->getChosenJournal()->getId(),
                )
            );
    }

    protected function runImport()
    {
        Registry::get('DataMappingManager')->setDataMappingDir(
            Registry::get('MigrationManager')->getChosenJournal()
        );

        $this->mapJournal(
            Registry::get('JournalHandler')->getJournalIdFromEntitiesDir()
        );

        // import the entities
        $this->importEntities(
            Registry::get('MigrationManager')->getEntitiesToMigrate()
                                             ->toArray()
        );
    }

    protected function reportTableExportation($table)
    {
        Registry::get('IoManager')->writeToStdout(
            implode('', array(
                'Exporting the "', $table, '"...',
            )),
            false,
            1,
            2
        );
    }

    protected function runExport()
    {
        // export the entities
        foreach (Registry::get(
            'MigrationManager'
        )->getEntitiesToMigrate()->toArray() as $table) {
            $this->reportTableExportation($table);
            $this->getHandler($table)->export(
                Registry::get('MigrationManager')->getChosenJournal()
            );
        }
    }

    protected function writeErrorToStdout($e)
    {
        Registry::get('IoManager')->writeToStdout(
            \implode(': ', array(
                'Caught the error',
                \implode(PHP_EOL, array(
                    $e->getMessage(),
                    $e->getTraceAsString(),
                ))
            )),
            false,
            2,
            2
        );
    }

    protected function writeExceptionToStdout($e)
    {
        Registry::get('IoManager')->writeToStdout(
            \implode(': ', array(
                'Caught the exception',
                $e->getMessage(),
            )),
            false,
            2,
            2
        );
    }

    protected function showLastError()
    {
        $lastError = \error_get_last();
        if ($lastError)
            var_dump($lastError);
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
            $this->writeExceptionToStdout($e);
        } catch (\Error $e) {
            $this->writeErrorToStdout($e);
        } finally {
            $this->showLastError();
            if ($this->isRunning())
                $this->endFlow();
        }
    }

}
