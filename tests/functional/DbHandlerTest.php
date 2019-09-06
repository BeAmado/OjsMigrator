<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\DbHandler;

class DbHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        require_once(dirname(__FILE__) . '/../TestStub.php');
        $this->dbHandlerStub = new class extends DbHandler {
            use \BeAmado\OjsMigrator\TestStub;
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
            $this->dbHandlerStub->callMethod(
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
