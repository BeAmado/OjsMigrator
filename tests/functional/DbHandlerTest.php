<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Db\DbHandler;
use BeAmado\OjsMigrator\StubInterface;
use BeAmado\OjsMigrator\Util\ConfigHandler;
use BeAmado\OjsMigrator\Util\FileSystemManager;

//////// traits ////////////
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\WorkWithFiles;
use BeAmado\OjsMigrator\WorkWithSqlite;

class DbHandlerTest extends FunctionalTest implements StubInterface
{
    use WorkWithFiles;
    use WorkWithSqlite;

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();
        (new class { use WorkWithSqlite; })->createDbSandbox();
    }

    public static function tearDownAfterClass() : void
    {
        parent::tearDownAfterClass();
        (new class { use WorkWithSqlite; })->removeDbSandbox();
    }

    public function getStub()
    {
        return new class extends DbHandler {
            use TestStub;
        };
    }

    public function testCanInstantiateDbHandler()
    {
        $this->assertInstanceOf(
            DbHandler::class,
            new DbHandler()
        );
    }

}
