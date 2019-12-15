<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Registry;

// interfaces
use BeAmado\OjsMigrator\StubInterface;

// traits
use BeAmado\OjsMigrator\TestStub;

//mocks
use BeAmado\OjsMigrator\JournalMock;
use BeAmado\OjsMigrator\GroupMock;

class GroupHandlerTest extends FunctionalTest
{
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();
        foreach (array(
            'journals',
            'users',
            'user_settings',
            'user_interests',
            'controlled_vocabs',
            'controlled_vocab_entries',
            'controlled_vocab_entry_settings',
            'roles',
            'groups',
            'group_settings',
            'group_memberships',
        ) as $table) {
            Registry::get('DbHandler')->createTableIfNotExists($table);
        }

        Registry::get('EntityHandler')->createOrUpdateInDatabase(
            (new JournalMock())->getTestJournal()
        );
    }

    public function __construct()
    {
        parent::__construct();
        $this->groupMock = new GroupMock();
    }

    protected function createGroupBacks()
    {
        return $this->groupMock->getGroupBacks();
    }

    protected function createGroupForwards()
    {
        return $this->groupMock->getGroupForwards();
    }

    public function testCanCreateMockedGroups()
    {
        $backs = $this->createGroupBacks();
        $forwards = $this->createGroupForwards();

        $testJournal = (new JournalMock())->getJournal('test_journal');
        $ironman = (new UserMock())->getUser('ironman');
        $batman = (new UserMock())->getUser('batman');
        $hulk = (new UserMock())->getUser('hulk');
        $thor = (new UserMock())->getUser('thor');
        $greenlantern = (new UserMock())->getUser('greenlantern');

        $this->assertSame(
            '1-1',
            implode('-', array(
                (int) $backs->get('assoc_id')
                            ->getValue() === $testJournal->get('journal_id')
                                                         ->getValue(),
                (int) $forwards->get('assoc_id')
                               ->getValue() === $testJournal->get('journal_id')
                                                            ->getValue(),
            ))
        );
    }
}
