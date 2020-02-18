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

    public function run($ojsDir = null)
    {
        try {
            $this->preload(array(
                'OjsDir' => $ojsDir,
            ));

            $this->beginFlow();

            echo "\n\nThe params:\n";
            var_dump(Registry::get('MigrationManager')->getMigrationOptionsAsArray());
            echo "\n\n";
        } catch (Exception $e) {
            echo "\n\nCaught the exception:\n'" . $e->getMessage() . "\n\n";
        } finally {
            $lastError = \error_get_last();
            if ($lastError)
                var_dump($lastError);

            $this->endFlow();
        }
    }

}
