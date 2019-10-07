<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\DbHandler;
use BeAmado\OjsMigrator\StubInterface;
use BeAmado\OjsMigrator\Util\ConfigHandler;

class DbHandlerTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends DbHandler {
            use BeAmado\OjsMigrator\TestStub;
            use BeAmado\OjsMigrator\WorkWithFiles;
        };
    }

    public function testCanInstantiateDbHandler()
    {
        $this->assertInstanceOf(
            DbHandler::class,
            new DbHandler()
        );
    }

    public function testConnectToMysql()
    {
        $connData = (new ConfigHandler($this->getStub()->getOjs2ConfigFile()))
            ->getConnectionSettings();

        $this->assertInstanceOf(
            \PDO::class,
            $this->getStub()->callMethod(
                'createMySqlConnection',
                array('args' => $connData)
            )
        );
    }
}
