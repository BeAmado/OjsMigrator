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
        Registry::get('SchemaHandler')->removeSchema();
        Registry::clear();
    }

    public function run($ojsDir = null)
    {
        $this->preload(array(
            'OjsDir' => $ojsDir,
        ));
        Registry::get('IoManager')->writeToStdout(
            '############### OJS journal migration #################' . PHP_EOL
        );
        $this->finish();
    }

}
