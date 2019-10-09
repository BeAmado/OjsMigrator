<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\DbHandler;
use BeAmado\OjsMigrator\StubInterface;
use BeAmado\OjsMigrator\Util\ConfigHandler;
use BeAmado\OjsMigrator\Util\FileSystemManager;

//////// traits ////////////
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\WorkWithFiles;
use BeAmado\OjsMigrator\WorkWithSqlite;

class DbHandlerTest extends TestCase implements StubInterface
{
    use WorkWithFiles;
    use WorkWithSqlite;

    public function __construct()
    {
        parent::__construct();
        if (!(new FileSystemManager())->dirExists($this->getDbSandbox())) {
            $this->createDbSandbox();
        }
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

    /**
     * @requires extension pdo_mysql
     */
    public function testConnectToMysql()
    {
        if (array_search('pdo_sqlite', get_loaded_extensions())) {
            $this->markTestSkipped('The driver used is sqlite');
        }

        $connData = (new ConfigHandler($this->getOjs2ConfigFile()))
            ->getConnectionSettings();

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
        if (array_search('pdo_sqlite', get_loaded_extensions())) {
            $this->markTestSkipped('The driver used is sqlite');
        }

        $connData = (new ConfigHandler($this->getOjs2ConfigFile()))
            ->getConnectionSettings();
        
        $this->assertInstanceOf(
            \PDO::class,
            (new DbHandler())->createConnection($connData)
        );
    }

    /**
     * @requires extension pdo_sqlite
     */
    public function testConnectToSqlite()
    {
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
        $this->assertInstanceOf(
            \PDO::class,
            (new DbHandler())->createConnection(array(
                'driver' => 'sqlite',
                'name' => $this->getSqliteDbFilename(),
            ))
        );
    }
}
