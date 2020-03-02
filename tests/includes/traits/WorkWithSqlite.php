<?php

namespace BeAmado\OjsMigrator\Test;
use \BeAmado\OjsMigrator\Registry;

trait WorkWithSqlite
{
    public function createDbSandbox()
    {
        if (!Registry::get('FileSystemManager')->dirExists(
            $this->getDbSandbox()
        ))
            Registry::get('FileSystemManager')->createDir(
                $this->getDbSandbox()
            );
    }

    public function removeDbSandbox()
    {
        Registry::get('FileSystemManager')->removeWholeDir(
            $this->getDbSandbox()
        );
    }

    public function getDbSandbox()
    {
        return Registry::get('FileSystemManager')->formPathFromBaseDir(array(
            'tests',
            '_data',
            'db_sandbox'
        ));
    }

    public function getSqliteDbFilename()
    {
        return $this->getDbSandbox() . 
            \BeAmado\OjsMigrator\DIR_SEPARATOR . 'tests_ojs2.db';
    }

    public function getAutoIncrement()
    {
        return 'AUTOINCREMENT';
    }
}
