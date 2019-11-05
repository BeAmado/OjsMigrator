<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Db\DbHandler;
use BeAmado\OjsMigrator\StubInterface;
use BeAmado\OjsMigrator\Util\ConfigHandler;
use BeAmado\OjsMigrator\Util\FileSystemManager;
use BeAmado\OjsMigrator\Registry;

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

    public function testCanCreateTableUserInterests()
    {
        Registry::get('DbHandler')->createTable('user_interests');

        $queryInsert = 'INSERT INTO user_interests '
            . '(user_id, controlled_vocab_entry_id) VALUES (1, 5), (10, 28)';
            //. '(user_id, controlled_vocab_entry_id) Values (:u, :c)';

        /*$insertStmt = Registry::get('StatementHandler')->create($queryInsert);

        $insertStmt->bindParams(
            array('user_id' => ':u', 'controlled_vocab_entry_id' => ':c'),
            Registry::get('MemoryManager')->create(array(
                'user_id' => 1,
                'controlled_vocab_entry_id' => 5,
            ))
        );
        $insertStmt->execute();

        $insertStmt->bindParams(
            array('user_id' => ':u', 'controlled_vocab_entry_id' => ':c'),
            Registry::get('MemoryManager')->create(array(
                'user_id' => 10,
                'controlled_vocab_entry_id' => 28,
            ))
        );
        $insertStmt->execute();*/

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

}
