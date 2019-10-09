<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\ConnectionManager;
use BeAmado\OjsMigrator\Registry;

class ConnectionManagerTest extends TestCase
{
    public function testSetConnection()
    {
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
