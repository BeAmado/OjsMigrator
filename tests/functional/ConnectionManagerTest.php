<?php

use BeAmado\OjsMigrator\Db\ConnectionManager;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\FunctionalTest;

// interfaces
use BeAmado\OjsMigrator\StubInterface;

// traits
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\WorkWithSqlite;

class ConnectionManagerTest extends FunctionalTest implements StubInterface
{
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
        return new class extends ConnectionManager {
            use TestStub;
        };
    }

    public function testSetConnection()
    {
        if (
            !array_search('pdo_sqlite', get_loaded_extensions()) &&
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

    /**
     * @requires extension pdo_mysql
     */
    public function testConnectToMysql()
    {
        /*if (array_search('pdo_sqlite', get_loaded_extensions())) {
            $this->markTestSkipped('The driver used is sqlite');
        }*/
        if (!array_search('pdo_mysql', get_loaded_extensions()))
            $this->markTestSkipped('The driver for mysql is not present');

        $connData = Registry::get('ConfigHandler')->getConnectionSettings();
        if ($connData['driver'] !== 'mysql')
            $this->markTestSkipped('Is not mysql');
        
        $connData = Registry::get('ConfigHandler')->getConnectionSettings();

        $this->assertInstanceOf(
            \PDO::class,
            $this->getStub()->callMethod(
                'createMySqlConnection',
                array('args' => $connData)
            )
        );
    }

    /**
     * @requires extension pdo_mysql
     */
    public function testCreateMysqlConnection()
    {
        /*if (array_search('pdo_sqlite', get_loaded_extensions())) {
            $this->markTestSkipped('The driver used is sqlite');
        }*/
        if (!array_search('pdo_mysql', get_loaded_extensions()))
            $this->markTestSkipped('The driver for mysql is not present');

        $connData = Registry::get('ConfigHandler')->getConnectionSettings();
        if ($connData['driver'] !== 'mysql')
            $this->markTestSkipped('Is not mysql');
        
        $this->assertInstanceOf(
            \PDO::class,
            $this->getStub()->callMethod(
                'createMySqlConnection',
                array(
                    'connData' => $connData
                )
            )
        );
    }

    /**
     * @requires extension pdo_sqlite
     */
    public function testConnectToSqlite()
    {
        if (!array_search('pdo_sqlite', get_loaded_extensions()))
            $this->markTestSkipped('The driver for sqlite is not present');
        
        $connData = Registry::get('ConfigHandler')->getConnectionSettings();
        if ($connData['driver'] !== 'sqlite')
            $this->markTestSkipped('Is not sqlite');
        
        $this->assertInstanceOf(
            \PDO::class,
            $this->getStub()->callMethod(
                'createSqliteConnection',
                $this->getSqliteDbFilename()
            )
        );
    }

    /**
     * @requires extension pdo_sqlite
     */
    public function testCreateSqliteConnection()
    {
        if (!array_search('pdo_sqlite', get_loaded_extensions()))
            $this->markTestSkipped('The driver for sqlite is not present');

        $connData = Registry::get('ConfigHandler')->getConnectionSettings();
        if ($connData['driver'] !== 'sqlite')
            $this->markTestSkipped('Is not sqlite');
        
        $this->assertInstanceOf(
            \PDO::class,
            Registry::get('ConnectionManager')->createConnection(array(
                'driver' => 'sqlite',
                'name' => $this->getSqliteDbFilename(),
            ))
        );
    }
}
