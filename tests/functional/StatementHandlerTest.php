<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Db\StatementHandler;
use BeAmado\OjsMigrator\Db\ConnectionManager;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\WorkWithOjsDir;

class StatementHandlerTest extends FunctionalTest
{
    use WorkWithOjsDir;

    public function testCanCreateAStatement()
    {
        if (
            !array_search('pdo_sqlite', get_loaded_extensions()) &&
            !array_search('pdo_mysql', get_loaded_extensions())
        ) {
            $this->markTestSkipped('None of the database drivers available');
        }

        $this->assertInstanceOf(
            \BeAmado\OjsMigrator\Db\MyStatement::class,
            (new StatementHandler())->create(
                'CREATE TABLE person (
                    `id` integer, 
                    `name` varchar(50), 
                    primary key (`id`)
                )'
            )
        );
    }

    public function testSetStatementInsertUsers()
    {
        Registry::get('StatementHandler')->setStatement('insertUsers');

        $this->assertInstanceOf(
            \BeAmado\OjsMigrator\Db\MyStatement::class,
            Registry::get('insertUsers')
        );
    }

    public function testGetStatementUpdateJournalSettings()
    {
        $stmt = Registry::get('StatementHandler')->getStatement(
            'updateJournalSettings'
        );

        $expectedQuery = 'UPDATE journal_settings '
            . 'SET '
            .     'setting_value = :updateJournalSettings_settingValue, '
            .     'setting_type = :updateJournalSettings_settingType '
            . 'WHERE '
            .     'journal_id = :updateJournalSettings_journalId AND '
            .     'locale = :updateJournalSettings_locale AND '
            .     'setting_name = :updateJournalSettings_settingName';

        $this->assertSame($expectedQuery, $stmt->getQuery());
    }

    public function testExecuteStatementInsertJournal()
    {
        Registry::get('DbHandler')->createTable('journals');
        Registry::get('StatementHandler')->execute(
            'insertJournals',
            Registry::get('EntityHandler')->create('journals', array(
                'path' => 'ma_nature',
                'primary_locale' => 'fr_CA',
            ))
        );

        $stmt = Registry::get('StatementHandler')->create(
            'SELECT * FROM journals'
        );

        $stmt->execute();

        Registry::remove('selectData');

        $stmt->fetch(function($res) {
            if ($res === null) 
                return;

            if (!Registry::hasKey('selectData'))
                Registry::set(
                    'selectData', 
                    Registry::get('MemoryManager')->create(array())
                );

            Registry::get('selectData')->push(new \BeAmado\OjsMigrator\Entity(
                $res,
                'journals'
            ));

            return true;
        });

        $journal = Registry::get('selectData')->get(0)->cloneInstance();
        Registry::remove('selectData');

        $this->assertSame(
            'ma_nature',
            $journal->getData('path')
        );

    }

    /**
     * @depends testExecuteStatementInsertJournal
     */
    public function testExecuteStatementSelectJournals()
    {
        Registry::remove('journalId');
        Registry::get('StatementHandler')->execute(
            'getlastJournals',
            null,
            function($res) {
                Registry::set('journalId', $res['journal_id']);
                return true;
            }
        );

        Registry::remove('selectData');

        Registry::get('StatementHandler')->execute(
            'selectJournals',
            Registry::get('EntityHandler')->create('journals', array(
                'journal_id' => Registry::get('journalId'),
            )),
            function ($res) {
                if (!Registry::hasKey('selectData'))
                    Registry::set(
                        'selectData',
                        Registry::get('MemoryManager')->create(array())
                    );

                Registry::get('selectData')->push(
                    Registry::get('EntityHandler')->create('journals', $res)
                );

                return true;
            }
        );

        $journals = Registry::get('selectData')->cloneInstance();

        Registry::remove('selectData');

        $this->assertTrue(
            count($journals->listKeys()) === 1 &&
            $journals->get(0)->getData('journal_id') == 1 &&
            $journals->get(0)->getData('seq') == 0 &&
            $journals->get(0)->getData('primary_locale') === 'fr_CA'
        );
    }
}
