<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Db\StatementHandler;
use BeAmado\OjsMigrator\Db\ConnectionManager;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Entity\Entity;
use BeAmado\OjsMigrator\WorkWithOjsDir;

class StatementHandlerTest extends FunctionalTest
{
    use WorkWithOjsDir;

    public static function setUpBeforeClass($args = array(
        'createTables' => array(
            'journals',
            'journal_settings',
            'users',
        ),
    )) : void { parent::setUpBeforeClass($args); }

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
        Registry::get('DbHandler')->createTableIfNotExists('users');
        Registry::get('StatementHandler')->setStatement('insertUsers');

        $this->assertInstanceOf(
            \BeAmado\OjsMigrator\Db\MyStatement::class,
            Registry::get('insertUsers')
        );
    }

    public function testGetStatementUpdateJournalSettings()
    {
        Registry::get('DbHandler')->createTableIfNotExists('journal_settings');
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
        Registry::get('DbHandler')->createTableIfNotExists('journals');
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

            Registry::get('selectData')->push(new Entity(
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
        Registry::remove('selectData');

        Registry::get('StatementHandler')->execute(
            'selectJournals',
            null,
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

    /**
     * @depends testExecuteStatementInsertJournal
     */
    public function testExecuteStatementSelectJournalByPath()
    {
        Registry::remove('selectData');
        Registry::get('StatementHandler')->removeStatement('selectJournals');

        Registry::get('StatementHandler')->execute(
            'selectJournals',
            Registry::get('MemoryManager')->create(array(
                'where' => array(
                    'path' => 'ma_nature',
                ),
            )),
            function($res) {
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
            $journals->get(0)->getData('path') === 'ma_nature' &&
            $journals->get(0)->getData('primary_locale') === 'fr_CA' &&
            $journals->get(0)->getData('enabled') == 1
        );
    }

    /**
     * @depends testExecuteStatementInsertJournal
     * @depends testExecuteStatementSelectJournalByPath
     */
    public function testExecuteStatementUpdateJournal()
    {
        Registry::get('StatementHandler')->execute(
            'updateJournals',
            Registry::get('EntityHandler')->create('journals',array(
                'journal_id' => 1,
                'path' => 'my_nature',
                'primary_locale' => 'en_NZ',
                'enabled' => 0
            ))
        );

        Registry::remove('selectData');
        Registry::get('StatementHandler')->removeStatement('selectJournals');

        Registry::get('StatementHandler')->execute(
            'selectJournals',
            null,
            function($res) {
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
            $journals->get(0)->getData('path') === 'my_nature' &&
            $journals->get(0)->getData('primary_locale') === 'en_NZ' &&
            $journals->get(0)->getData('enabled') == 0
        );
    }

    public function testInsert3JournalSettings()
    {
        Registry::get('DbHandler')->createTableIfNotExists('journal_settings');

        // insert 3 journal_settings for the journal with id 1
        $settings = array(
            Registry::get('EntityHandler')->create('journal_settings', array(
                'journal_id' => 1,
                'locale' => 'fr_CA',
                'setting_name' => 'title',
                'setting_value' => 'Tous les animaux',
                'setting_type' => 'string',
            )),
            Registry::get('EntityHandler')->create('journal_settings', array(
                'journal_id' => 1,
                'locale' => 'en_NZ',
                'setting_name' => 'title',
                'setting_value' => 'All the animals',
                'setting_type' => 'string',
            )),
            Registry::get('EntityHandler')->create('journal_settings', array(
                'journal_id' => 1,
                'locale' => 'es_AR',
                'setting_name' => 'title',
                'setting_value' => 'Todos los animales',
                'setting_type' => 'string',
            )),
        );

        foreach ($settings as $setting) {
            Registry::get('StatementHandler')->execute(
                'insertJournalSettings', 
                $setting
            );
        }

        Registry::remove('selectData');
        Registry::get('StatementHandler')->execute(
            'selectJournalSettings',
            null,
            function($res) {
                if (!Registry::hasKey('selectData'))
                    Registry::set(
                        'selectData',
                        Registry::get('MemoryManager')->create(array())
                    );

                Registry::get('selectData')->push(
                    Registry::get('EntityHandler')->create(
                        'journal_settings',
                        $res
                    )
                );

                return true;
            }
        );

        $journalSettings = Registry::get('selectData')->cloneInstance();
        Registry::remove('selectData');

        $this->assertTrue(
            count($journalSettings->listKeys()) === 3 &&
            in_array(
                $journalSettings->get(0)->getData('locale'),
                array('es_AR', 'en_NZ', 'fr_CA')
            ) &&
            in_array(
                $journalSettings->get(1)->getData('setting_value'),
                array(
                    'Tous les animaux',
                    'All the animals',
                    'Todos los animales',
                )
            ) &&
            $journalSettings->get(0)->getData('setting_name') === 'title' &&
            $journalSettings->get(1)->getData('setting_type') === 'string' &&
            $journalSettings->get(2)->getData('journal_id') == 1
        );
    }

    /**
     * @depends testInsert3JournalSettings
     */
    public function testExecuteStatementDeleteJournalTitleInSpanish()
    {
        //$this->markTestSkipped('pending');
        Registry::get('StatementHandler')->execute(
            'deleteJournalSettings',
            array(
                'journal_id' => 1,
                'locale' => 'es_AR',
                'setting_name' => 'title',
            )
        );

        Registry::remove('selectData');
        Registry::get('StatementHandler')->execute(
            'selectJournalSettings',
            null,
            function($res) {
                if (!Registry::hasKey('selectData'))
                    Registry::set(
                        'selectData',
                        Registry::get('MemoryManager')->create(array())
                    );

                Registry::get('selectData')->push(
                    Registry::get('EntityHandler')->create(
                        'journal_settings',
                        $res
                    )
                );

                return true;
            }
        );

        $journalSettings = Registry::get('selectData')->cloneInstance();
        Registry::remove('selectData');

        $this->assertTrue(
            count($journalSettings->listKeys()) === 2 &&
            in_array(
                $journalSettings->get(0)->getData('setting_value'),
                array(
                    'All the animals',
                    'Tous les animaux',
                )
            ) &&
            in_array(
                $journalSettings->get(1)->getData('setting_value'),
                array(
                    'All the animals',
                    'Tous les animaux',
                )
            )
        );
    }

    /**
     * @depends testInsert3JournalSettings
     */
    public function testDeleteAllJournalSettingsFromJournalId1()
    {
        Registry::get('StatementHandler')->removeStatement(
            'deleteJournalSettings'
        );

        Registry::get('StatementHandler')->execute(
            'deleteJournalSettings',
            array(
                'where' => array(
                    'journal_id' => 1
                ),
            )
        );

        $statement = Registry::get('StatementHandler')->create(
            'SELECT COUNT(1) AS count FROM journal_settings'
        );

        Registry::remove('count');
        Registry::get('StatementHandler')->execute(
            $statement,
            null,
            function($res) {
                Registry::set('count', $res['count']);
            }
        );

        $this->assertSame('0', Registry::get('count'));
    }
}
