<?php

namespace BeAmado\OjsMigrator;

class Application
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var \BeAmado\OjsMigrator\MyObject
     */
    private $parameters;

    public function __construct($args = array())
    {
        $this->parameters = Registry::get('MemoryManager')->create(
            $this->filterParameters($args)
        );
        $this->status = 'idle';
    }

    protected function defaultParameters()
    {
        return array(
            'OjsDir' => null,
            'clearRegistry' => true,
        );
    }

    protected function filterParameters($args = array())
    {
        if (!\is_array($args) || empty($args))
            return $this->defaultParameters();
        
        $filtered = array();
        foreach ($this->defaultParameters() as $key => $value) {
            if (\array_key_exists($key, $args))
                $filtered[$key] = $args[$key];
            else
                $filtered[$key] = $value;
        }

        if (\array_key_exists('cli', $args))
            return Registry::get('ArrayHandler')->union(
                $this->filterCommandLineArgs($args['cli']),
                $filtered
            );
        else
            return $filtered;
    }

    protected function filterCommandLineArgs($cliArgs)
    {
        if (!\is_array($cliArgs))
            return array();
    }

    /**
     * Gets the parameters, readonly
     *
     * @return \BeAmado\OjsMigrator\MyObject
     */
    protected function getParameters()
    {
        return $this->parameters->cloneInstance();
    }

    protected function getParameter($name)
    {
        if ($this->getParameters()->hasAttribute($name))
            return $this->getParameters()->get($name)->getValue();
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

    protected function preload()
    {
        $this->start();
        Maestro::setOjsDir($this->getParameter('OjsDir'));
        Maestro::setSchemaDir();
        Registry::get('SchemaHandler')->loadAllSchema();
    }

    protected function finish()
    {
        Registry::get('SchemaHandler')->removeSchemaDir();
        if ($this->getParameter('clearRegistry'))
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

        Registry::get('MigrationManager')->chooseEntitiesToMigrate(
            $this->entitiesOrder()
        );
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
            $this->submissionsTableName(),
            'users',
        )))
            return;

        if ($table === $this->submissionsTableName())
            return Registry::get('SubmissionHandler');

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
    }

    protected function submissionsTableName()
    {
        return Registry::get('SubmissionHandler')->formTableName();
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
            8 => $this->submissionsTableName(),
            9 => 'keywords',
        );
    }

    protected function exportEntities($entities, $journal)
    {
        foreach($this->entitiesOrder() as $tableName) {
            if ($tableName === 'keywords')
                continue;

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
            $tableName = $this->submissionsTableName();

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

    protected function importKeywords()
    {
        foreach(\array_map(
            function($submissionDir) {
                return \basename($submissionDir);
            },
            $this->listEntityDataDir($this->submissionsTableName()) ?: array()
        ) as $smId) {
            Registry::get('SubmissionKeywordHandler')->importKeywords($smId);
        }
    }

    protected function importEntity($tableName)
    {
        $this->reportTableImportation($tableName);

        if ($tableName === 'journals')
            return $this->importJournal();
        else if ($tableName === 'keywords')
            return $this->importKeywords();

        foreach ($this->getEntityFilesToImport(
            $tableName
        ) ?: array() as $filename) {
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
        )->getEntitiesToMigrate()->toArray() ?: array() as $table) {
            if ($table === 'keywords')
                continue;

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

    public function run()
    {
        try {
            $this->preload();
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
