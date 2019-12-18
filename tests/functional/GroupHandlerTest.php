<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Entity\GroupHandler;

// interfaces
use BeAmado\OjsMigrator\StubInterface;

// traits
use BeAmado\OjsMigrator\TestStub;

//mocks
use BeAmado\OjsMigrator\JournalMock;
use BeAmado\OjsMigrator\GroupMock;
use BeAmado\OjsMigrator\UserMock;

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

    protected function importUsers()
    {
        foreach (array(
            'ironman',
            'batman',
            'hulk',
            'thor',
            'hawkeye',
            'greenlantern',
        ) as $username) {
            Registry::get('UserHandler')->importUser(
                (new UserMock())->getUser($username)
            );
        }
    }

    public function getStub()
    {
        return new class extends GroupHandler {
            use TestStub;
        };
    }

    public function __construct()
    {
        parent::__construct();
        $this->groupMock = new GroupMock();
    }

    protected function createGroupBacks()
    {
        return $this->getStub()->create($this->groupMock->getGroupBacks());
    }

    protected function createGroupForwards()
    {
        return $this->getStub()->create($this->groupMock->getGroupForwards());
    }

    public function testCanCreateMockedGroups()
    {
        $backs = $this->createGroupBacks();
        $forwards = $this->createGroupForwards();

        $testJournal = (new JournalMock())->getJournal('test_journal');
        $ironman = (new UserMock())->getUser('ironman');
        //$batman = (new UserMock())->getUser('batman');
        //$hulk = (new UserMock())->getUser('hulk');
        $thor = (new UserMock())->getUser('thor');
        //$greenlantern = (new UserMock())->getUser('greenlantern');

        $this->assertSame(
            '1-1-1-1-1-1',
            implode('-', array(
                (int) ($backs->get('assoc_id')
                            ->getValue()
                   === $testJournal->get('journal_id')
                                  ->getValue()),
                (int) ($forwards->get('assoc_id')
                                ->getValue() 
                   === $testJournal->get('journal_id')
                                   ->getValue()),
                (int) ($backs->get('settings')
                             ->get(0)
                             ->get('setting_value')
                             ->getValue() === 'backs'),
                (int) ($forwards->get('settings')
                                ->get(0)
                                ->get('setting_value')
                                ->getValue() === 'forwards'),
                (int) ($backs->get('memberships')
                             ->get(0)
                             ->get('user_id')
                             ->getValue() 
                  === $ironman->get('user_id')->getValue()),
                (int) ($forwards->get('memberships')
                                ->get(-1)
                                ->get('user_id')
                                ->getValue() 
                  === $thor->get('user_id')->getValue()),
            ))
        );
    }

    /**
     * @depends testCanCreateMockedGroups
     */
    public function testCanRegisterGroupForwards()
    {
        $forwards = $this->createGroupForwards();

        $registered = $this->getStub()->callMethod(
            'registerGroup',
            $forwards
        );

        $group = Registry::get('GroupsDAO')->read(array(
            'group_id' => Registry::get('DataMapper')->getMapping(
                'groups',
                $forwards->getId()
            )
        ))->get(0);

        $journal = Registry::get('JournalsDAO')->read(array(
            'path' => 'test_journal',
        ))->get(0);

        $this->assertSame(
            '1-1-1-' . $journal->getId(),
            implode('-', array(
                (int) $registered,
                (int) Registry::get('DataMapper')->isMapped(
                    'groups', 
                    $forwards->getId()
                ),
                (int) Registry::get('EntityHandler')->areEqual(
                    $forwards, 
                    $group, 
                    array('assoc_id')
                ),
                $group->getData('assoc_id')
            ))
        );
    }

    /**
     * @depends testCanRegisterGroupForwards
     */
    public function testCanImportGroupSetting()
    {
        $forwards = $this->createGroupForwards();

        $imported = $this->getStub()->callMethod(
            'importGroupSetting',
            $forwards->getData('settings')[0]
        );

        $forwards->get('settings')->get(0)->set(
            'group_id',
            Registry::get('DataMapper')->getMapping(
                'groups',
                $forwards->getId()
            )
        );

        $setting = Registry::get('GroupSettingsDAO')->read(
            $forwards->getData('settings')[0]
        )->get(0);

        $this->assertSame(
            '1-1',
            implode('-', array(
                (int) $imported,
                (int) Registry::get('EntityHandler')->areEqual(
                    $forwards->get('settings')->get(0),
                    $setting
                )
            ))
        );
    }

    /**
     * @depends testCanRegisterGroupForwards
     */
    public function testCanImportGroupMembership()
    {
        $this->importUsers();

        $forwards = $this->createGroupForwards();

        $imported = $this->getStub()->callMethod(
            'importGroupMembership',
            $forwards->get('memberships')->get(0)
        );

        $memberships = Registry::get('GroupMembershipsDAO')->read(array(
            'group_id' => Registry::get('DataMapper')->getMapping(
                'groups',
                $forwards->getId()
            )
        ));

        $hulk = (new UserMock())->getUser('hulk');

        $this->assertSame(
            '1-1-' . Registry::get('DataMapper')->getMapping(
                'users',
                $hulk->get('user_id')->getValue()
            ),
            implode('-', array(
                (int) $imported,
                $memberships->length(),
                $memberships->get(0)->getData('user_id')
            ))
        );
    }

    /**
     * @depends testCanRegisterGroupForwards
     * @depends testCanImportGroupSetting
     * @depends testCanImportGroupMembership
     */
    public function testCanImportGroupForwards()
    {
        $forwards = $this->createGroupForwards();

        $imported = Registry::get('GroupHandler')->importGroup($forwards);

        $settings = Registry::get('GroupSettingsDAO')->read(array(
            'group_id' => Registry::get('DataMapper')->getMapping(
                'groups',
                $forwards->getId()
            )
        ));

        $memberships = Registry::get('GroupMembershipsDAO')->read(array(
            'group_id' => Registry::get('DataMapper')->getMapping(
                'groups',
                $forwards->getId()
            )
        ));

        $this->assertSame(
            '1-1-2',
            implode('-', array(
                (int) $imported,
                $settings->length(),
                $memberships->length(),
            ))
        );
    }

    /**
     * @depends testCanImportGroupForwards
     */
    public function testCanImportGroupBacks()
    {
        $backs = $this->createGroupBacks();

        $imported = Registry::get('GroupHandler')->importGroup($backs);

        $settings = Registry::get('GroupSettingsDAO')->read(array(
            'group_id' => Registry::get('DataMapper')->getMapping(
                'groups',
                $backs->getId()
            )
        ));

        $memberships = Registry::get('GroupMembershipsDAO')->read(array(
            'group_id' => Registry::get('DataMapper')->getMapping(
                'groups',
                $backs->getId()
            )
        ));

        $this->assertSame(
            '1-1-4',
            implode('-', array(
                (int) $imported,
                $settings->length(),
                $memberships->length(),
            ))
        );
    }
}
