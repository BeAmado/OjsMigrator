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

    public function run($ojsDir = null)
    {
        $this->preload(array(
            'OjsDir' => $ojsDir,
        ));

        $this->showWelcomeMessage();

        Registry::get('MigrationManager')->setImportExportAction();

        echo "\n\nThe params:\n";
        var_dump(Registry::get('MigrationManager')->getMigrationOptionsAsArray());
        echo "\n\n";

        $this->finish();
        $this->showEndMessage();
    }

}
