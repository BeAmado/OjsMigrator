<?php

namespace BeAmado\OjsMigrator;

trait WorkWithSqlite
{
    public function createDbSandbox()
    {
        (new \BeAmado\OjsMigrator\Util\FileSystemManager())->createDir(
            $this->getDbSandbox()
        );
    }

    public function removeDbSandbox()
    {
        (new \BeAmado\OjsMigrator\Util\FileSystemManager())->removeWholeDir(
            $this->getDbSandbox()
        );
    }

    public function getDbSandbox()
    {
        return (new \BeAmado\OjsMigrator\Util\FileSystemManager())
               ->formPathFromBaseDir(array('tests', '_data', 'db_sandbox'));
    }

    public function getSqliteDbFilename()
    {
        return $this->getDbSandbox()
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'tests_ojs2.db';
    }

    public function getAutoIncrement()
    {
        return 'AUTOINCREMENT';
    }
}
