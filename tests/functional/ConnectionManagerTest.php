<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\ConnectionManager;
use BeAmado\OjsMigrator\Registry;

class ConnectionManagerTest extends TestCase
{
    public function testSetConnection()
    {
        if (
            !array_search('pdo_sqlite', get_loaded_extensions()) ||
            !array_search('pdo_mysql', get_loaded_extensions())
        ) {
            $this->markTestSkipped('None of the database drivers available');
        }

        (new ConnectionManager())->setConnection();

        $this->assertInstanceOf(
            \PDO::class,
            Registry::get('connection')
        );
    }

    /**
     * @depends testSetConnection
     */
    public function testGetConnection()
    {
        $this->assertInstanceOf(
            \PDO::class,
            (new ConnectionManager())->getConnection()
        );
    }

    /**
     * @depends testGetConnection
     */
    public function testCloseConnection()
    {
        (new ConnectionManager())->closeConnection();
        $this->assertFalse(Registry::hasKey('connection'));
    }
}
