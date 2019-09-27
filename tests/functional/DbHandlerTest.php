<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\DbHandler;
use BeAmado\OjsMigrator\StubInterface;

class DbHandlerTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends DbHandler {
            use BeAmado\OjsMigrator\TestStub;
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
        $this->assertInstanceOf(
            \PDO::class,
            $this->getStub()->callMethod(
                'createMySqlConnection',
                array(
                    'args' => array(
                        'host' => 'localhost',
                        'db' => 'humanas',
                        'user' => 'ojs_user',
                        'pass' => 'ojs',
                    )
                )
            )
        );
    }
}
