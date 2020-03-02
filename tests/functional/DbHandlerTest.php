<?php

use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Db\DbHandler;
use BeAmado\OjsMigrator\Util\ConfigHandler;
use BeAmado\OjsMigrator\Util\FileSystemManager;
use BeAmado\OjsMigrator\Registry;

// interfaces
use BeAmado\OjsMigrator\Test\StubInterface;

// traits 
use BeAmado\OjsMigrator\Test\TestStub;
use BeAmado\OjsMigrator\Test\WorkWithFiles;
use BeAmado\OjsMigrator\Test\WorkWithSqlite;

class DbHandlerTest extends FunctionalTest implements StubInterface
{
    use WorkWithFiles;
    use WorkWithSqlite;

    public static function setUpBeforeClass($args = array()) : void
    {
        parent::setUpBeforeClass();
        (new class { use WorkWithSqlite; })->createDbSandbox();
    }

    public static function tearDownAfterClass($args = array()) : void
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

    public function testCanCreateTableUserInterests()
    {
        Registry::get('DbHandler')->createTable('user_interests');

        $queryInsert = 'INSERT INTO user_interests '
            . '(user_id, controlled_vocab_entry_id) VALUES (1, 5), (10, 28)';

        Registry::get('StatementHandler')->create($queryInsert)->execute();

        $querySelect = 'SELECT * FROM user_interests';

        $stmt = Registry::get('StatementHandler')->create($querySelect);
        $stmt->execute();

        Registry::remove('selectData');

        $stmt->fetch(function($res) {
            if ($res === null)
                return false;

            if (!Registry::hasKey('selectData'))
                Registry::set(
                    'selectData', 
                    Registry::get('MemoryManager')->create(array())
                );

            Registry::get('selectData')->push($res);
            return true;
        });

        $data = Registry::get('selectData')->toArray();
        Registry::remove('selectData');


        $this->assertEquals(
            array(
                array('user_id' => 1, 'controlled_vocab_entry_id' => 5),
                array('user_id' => 10, 'controlled_vocab_entry_id' => 28),
            ),
            $data
        );
    }

    /**
     * @depends testCanCreateTableUserInterests
     */
    public function testCanSeeThatTableUserInterestsExists()
    {
        $this->assertTrue(
            Registry::get('DbHandler')->tableExists('user_interests')
        );
    }

    /**
     * @depends testCanCreateTableUserInterests
     */
    public function testCanDropTheTableUserInterests()
    {
        $existedBefore = Registry::get('DbHandler')->tableExists(
            'user_interests'
        );

        $this->getStub()->callMethod('dropTable', 'user_interests');

        $existsNow = Registry::get('DbHandler')->tableExists(
            'user_interests'
        );

        $this->assertTrue($existedBefore && !$existsNow);
    }

    public function testCreateTableAuthSourcesWithCreateIfTableNotExists()
    {
        $existedBefore = Registry::get('DbHandler')->tableExists(
            'auth_sources'
        );

        Registry::get('DbHandler')->createTableIfNotExists('auth_sources');

        $existsNow = Registry::get('DbHandler')->tableExists('auth_sources');

        $this->assertTrue(!$existedBefore && $existsNow);
    }

    /**
     * @depends testCreateTableAuthSourcesWithCreateIfTableNotExists
     */
    public function testDoesNotCreateAgainATableThatExistsNorThrowsAnyError()
    {
        $existedBefore = Registry::get('DbHandler')->tableExists(
            'auth_sources'
        );

        Registry::get('DbHandler')->createTableIfNotExists('auth_sources');

        $existsNow = Registry::get('DbHandler')->tableExists('auth_sources');

        $this->assertTrue($existedBefore && $existsNow);
    }

    /**
     * @depends testCreateTableAuthSourcesWithCreateIfTableNotExists
     */
    public function testDropTableAuthSourcesWithDropTableIfExists()
    {
        $existedBefore = Registry::get('DbHandler')->tableExists(
            'auth_sources'
        );

        Registry::get('DbHandler')->dropTableIfExists('auth_sources');

        $existsNow = Registry::get('DbHandler')->tableExists('auth_sources');

        $this->assertTrue($existedBefore && !$existsNow);
    }

    /**
     * @depends testDropTableAuthSourcesWithDropTableIfExists
     */
    public function testDoesThrowAnyErrorWhenTryingToDropANonExistantTable()
    {
        $existedBefore = Registry::get('DbHandler')->tableExists(
            'auth_sources'
        );

        Registry::get('DbHandler')->dropTableIfExists('auth_sources');

        $existsNow = Registry::get('DbHandler')->tableExists('auth_sources');

        $this->assertTrue(!$existedBefore && !$existsNow);
    }
}
