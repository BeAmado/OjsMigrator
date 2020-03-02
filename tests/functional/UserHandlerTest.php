<?php

use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Entity\UserHandler;
use BeAmado\OjsMigrator\Test\UserMock;

// interfaces 
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;

class UserHandlerTest extends FunctionalTest implements StubInterface
{
    public function __construct()
    {
        parent::__construct();
        $this->userMock = new UserMock();
        $this->importedUsers = 0;
    }

    public static function setUpBeforeClass($args = array(
        'createTables' => array(
            'users',
            'user_settings',
            'user_interests',
            'controlled_vocabs',
            'controlled_vocab_entries',
            'controlled_vocab_entry_settings',
            'roles',
            'journals',
        ),
    )) : void {
        parent::setUpBeforeClass($args);

        $eh = Registry::get('EntityHandler');
        $eh->createOrUpdateInDatabase($eh->create('journals', array(
            'journal_id' => 178,
            'path' => 'test_journal',
        )));
    }

    public function getStub()
    {
        return new class extends UserHandler {
            use TestStub;
        };
    }

    public function testCanGetMockedUsers()
    {
        $ironman = $this->userMock->getUser('IronMan');
        $hulk = $this->userMock->getUser('hulk');
        $batman = $this->userMock->getUser('Batman');
        $hawkeye = $this->userMock->getUser('HAWKEYE');
        $greenlantern = $this->userMock->getUser('GreenLantern');
        $johnstewart = $this->userMock->getUser('stewart');
        $thor = $this->userMock->getUser('thor');

        $this->assertSame(
            'Stark-Banner-Bruce-Clint-Jordan-Stewart-Donald',
            implode('-', array(
                $ironman->get('last_name')->getValue(),
                $hulk->get('last_name')->getValue(),
                $batman->get('first_name')->getValue(),
                $hawkeye->get('first_name')->getValue(),
                $greenlantern->get('last_name')->getValue(),
                $johnstewart->get('last_name')->getValue(),
                $thor->get('first_name')->getValue(),
            ))
        );
    }

    public function testCreateUserIronMan()
    {
        $ironman = Registry::get('UserHandler')->create(
            $this->userMock->getUser('IronMan')
        );

        $this->assertSame(
            'Anthony-Stark',
            implode('-', array(
                $ironman->getData('first_name'),
                $ironman->getData('last_name'),
            ))
        );
    }

    protected function createIronMan()
    {
        return Registry::get('UserHandler')->create(
            $this->userMock->getUser('IronMan')
        );
    }

    protected function createBatman()
    {
        return Registry::get('UserHandler')->create(
            $this->userMock->getUser('Batman')
        );
    }

    protected function createHawkeye()
    {
        return Registry::get('UserHandler')->create(
            $this->userMock->getUser('Hawkeye')
        );
    }

    protected function createHulk()
    {
        return Registry::get('UserHandler')->create(
            $this->userMock->getUser('Hulk')
        );
    }

    protected function createGreenLantern()
    {
        return Registry::get('UserHandler')->create(
            $this->userMock->getUser('GreenLantern')
        );
    }

    protected function createJohnStewart()
    {
        return Registry::get('UserHandler')->create(
            $this->userMock->getUser('stewart')
        );
    }

    public function testCanCreateMockedUsers()
    {
        $this->assertSame(
            'Stark-Wayne-Banner-Barton-greenlantern/Jordan-greenlantern/Stewart',
            implode('-', array(
                $this->createIronMan()->getData('last_name'),
                $this->createBatman()->getData('last_name'),
                $this->createHulk()->getData('last_name'),
                $this->createHawkeye()->getData('last_name'),
                ''
                . $this->createGreenLantern()->getData('username')
                . '/' . $this->createGreenLantern()->getData('last_name'),
                ''
                . $this->createJohnStewart()->getData('username')
                . '/' . $this->createJohnStewart()->getData('last_name'),
            ))
        );
    }

    public function testRegisterUserIronMan()
    {
        $registered = $this->getStub()->callMethod(
            'registerUser',
            Registry::get('UserHandler')->create($this->userMock
                                                      ->getUser('ironman'))
        );

        $ironman = Registry::get('UsersDAO')->read(array(
            'username' => 'ironman',
        ))->get(0);

        $this->assertSame(
            '1-Anthony-Stark',
            implode('-', array(
                $registered,
                $ironman->getData('first_name'),
                $ironman->getData('last_name'),
            ))
        );
    }

    /**
     * @depends testRegisterUserIronMan
     */
    public function testCanSeeThatIronManIsAlreadyRegistered()
    {
        $this->assertTrue($this->getStub()->callMethod(
            'userIsAlreadyRegistered',
            $this->createIronMan()
        ));
    }

    /**
     * @depends testCanSeeThatIronManIsAlreadyRegistered
     */
    public function testCanImportIronManUserSetting()
    {
        $ironman = $this->createIronMan();
        $setting = $ironman->getData('settings')[0];
        $imported = $this->getStub()->callMethod(
            'importUserSetting',
            $setting
        );

        $setting->set(
            'user_id',
            Registry::get('DataMapper')->getMapping(
                'users', 
                $setting->get('user_id')->getValue()
            )
        );

        $fromDb = Registry::get('UserSettingsDAO')->read($setting)->get(0);

        $this->assertSame(
            '1-pt_BR-' . Registry::get('DataMapper')->getMapping(
                'users', 
                $ironman->getId()
            ),
            implode('-', array(
                $imported, 
                $fromDb->getData('locale'),
                $fromDb->getData('user_id'),
            ))
        );

    }

    /**
     * @depends testCanSeeThatIronManIsAlreadyRegistered
     */
    public function testCanImportIronManUserInterest()
    {
        $interest = $this->createIronMan()->getData('interests')[0];
        $imported = $this->getStub()->callMethod(
            'importUserInterest',
            $interest
        );

        $entrySettings = Registry::get('ControlledVocabEntrySettingsDAO')->read(
            array(
                'controlled_vocab_entry_id' => Registry::get('DataMapper')
                    ->getMapping(
                        'controlled_vocab_entries',
                        $interest->get('controlled_vocab_entry_id')->getValue()
                    ),
            )
        );

        $this->assertSame(
            '1-1-1-1-1',
            implode('-', array(
                (int) $imported,
                (int) Registry::get('DataMapper')->isMapped(
                    'controlled_vocab_entries',
                    $interest->get('controlled_vocab_entry_id')->getValue()
                ),
                (int) Registry::get('DataMapper')->isMapped(
                    'controlled_vocabs',
                    $interest->get('controlled_vocab_entries')
                             ->get(0)
                             ->get('controlled_vocab_id')->getValue()
                ),
                (int) $this->areEqual($entrySettings->length(), 1),
                (int) $this->areEqual(
                    $entrySettings->get(0)->getData('setting_value'),
                    'science'
                )
            ))
        );
    }

    /**
     * @depends testCanSeeThatIronManIsAlreadyRegistered
     */
    public function testCanImportIronManUserRole()
    {
        $ironman = $this->createIronMan();
        $role = $ironman->getData('roles')[0];

        $imported = $this->getStub()->callMethod(
            'importUserRole',
            $role
        );

        $roles = Registry::get('RolesDAO')->read(array(
            'role_id' => '' . $role->get('role_id')->getValue(),
            'journal_id' => Registry::get('DataMapper')->getMapping(
                'journals',
                $role->get('journal_id')->getValue()
            ),
            'user_id' => Registry::get('DataMapper')->getMapping(
                'users',
                $role->get('user_id')->getValue()
            ),
        ));

        $this->assertSame(
            '1-1', 
            implode('-', array(
                $imported,
                $roles->length(),
            ))
        );
    }

    /**
     * @depends testCanImportIronManUserRole
     */
    public function testCanImportAnotherIronManUserRole()
    {
        $ironman = $this->createIronMan();
        $role = $ironman->getData('roles')[1];

        $imported = $this->getStub()->callMethod(
            'importUserRole',
            $role
        );
        
        $role->set(
            'journal_id',
            Registry::get('DataMapper')->getMapping(
                'journals',
                $role->get('journal_id')->getValue()
            )
        );

        $role->set(
            'user_id',
            Registry::get('DataMapper')->getMapping(
                'users',
                $role->get('user_id')->getValue()
            )
        );

        $candidates = Registry::get('RolesDAO')->read($role);
        $roles = Registry::get('RolesDAO')->read();

        $this->assertSame(
            '1-1-256-2', 
            implode('-', array(
                $imported,
                $candidates->length(),
                $candidates->get(0)->getData('role_id'),
                $roles->length(),
            ))
        );
    }

    public function testCanImportUserIronMan()
    {
        $ironman = $this->createIronMan();

        $imported = Registry::get('UserHandler')->importUser($ironman);

        $settings = Registry::get('UserSettingsDAO')->read(array(
            'user_id' => Registry::get('DataMapper')->getMapping(
                'users',
                $ironman->getId()
             )
        ));

        $roles = Registry::get('RolesDAO')->read(array(
            'user_id' => Registry::get('DataMapper')->getMapping(
                'users',
                $ironman->getId()
             )
        ));

        $interests = Registry::get('UserInterestsDAO')->read(array(
            'user_id' => Registry::get('DataMapper')->getMapping(
                'users',
                $ironman->getId()
             )
        ));

        $this->assertSame(
            '1-4-3-3',
            implode('-', array(
                $imported,
                $settings->length(),
                $roles->length(),
                $interests->length(),
            ))
        );

        if ($imported)
            $this->importedUsers++;
    }

    /**
     * @depends testCanImportUserIronMan
     */
    public function testCanImportUserBatman()
    {
        $batman = $this->createBatman();

        $imported = Registry::get('UserHandler')->importUser($batman);

        $mapping = array(
            'user_id' => Registry::get('DataMapper')->getMapping(
                'users',
                $batman->getId()
            )
        );

        $batmanSettings = Registry::get('UserSettingsDAO')->read($mapping);
        $batmanRoles = Registry::get('RolesDAO')->read($mapping);
        $batmanInterests = Registry::get('UserInterestsDAO')->read($mapping);
        $roles = Registry::get('RolesDAO')->read();
        $settings = Registry::get('UserSettingsDAO')->read();
        $interests = Registry::get('UserInterestsDAO')->read();
        $controlledVocabs = Registry::get('ControlledVocabsDAO')->read();
        $entries = Registry::get('ControlledVocabEntriesDAO')->read();

        $this->assertSame(
            '1-4-3-3-8-6-6-4-4',
            implode('-', array(
                $imported,
                $batmanSettings->length(),
                $batmanRoles->length(),
                $batmanInterests->length(),
                $settings->length(),
                $roles->length(),
                $interests->length(),
                $controlledVocabs->length(),
                $entries->length(),
            ))
        );

        if ($imported)
            $this->importedUsers++;
    }

    /**
     * @depends testCanImportUserBatman
     */
    public function testCanImportUserGreenLantern()
    {
        $lantern = $this->createGreenLantern();

        $imported = Registry::get('UserHandler')->importUser($lantern);

        $mapping = array(
            'user_id' => Registry::get('DataMapper')->getMapping(
                'users',
                $lantern->getId()
            )
        );

        $lanternSettings = Registry::get('UserSettingsDAO')->read($mapping);
        $lanternRoles = Registry::get('RolesDAO')->read($mapping);
        $lanternInterests = Registry::get('UserInterestsDAO')->read($mapping);
        $roles = Registry::get('RolesDAO')->read();
        $settings = Registry::get('UserSettingsDAO')->read();
        $interests = Registry::get('UserInterestsDAO')->read();
        $controlledVocabs = Registry::get('ControlledVocabsDAO')->read();
        $entries = Registry::get('ControlledVocabEntriesDAO')->read();

        $this->assertSame(
            '1-3-1-3-11-7-9-4-4',
            implode('-', array(
                $imported,
                $lanternSettings->length(),
                $lanternRoles->length(),
                $lanternInterests->length(),
                $settings->length(),
                $roles->length(),
                $interests->length(),
                $controlledVocabs->length(),
                $entries->length(),
            ))
        );

        if ($imported)
            $this->importedUsers++;
    }

    /**
     * @depends testCanImportUserGreenLantern
     */
    public function testCanImportJohnStewartByChangingHisUsernameFromGreenlanternToGreenlantern2()
    {
        $johnStewart = $this->createJohnStewart();

        $imported = Registry::get('UserHandler')->importUser($johnStewart);

        $mapping = array(
            'user_id' => Registry::get('DataMapper')->getMapping(
                'users',
                $johnStewart->getId()
            )
        );

        $users = Registry::get('UsersDAO')->read();
        $search = Registry::get('UsersDAO')->read(array(
            'email' => $johnStewart->getData('email'),
        ));

        $user = $search->get(0);
        $stewartInterests = Registry::get('UserInterestsDAO')->read($mapping);

        $entry0 = Registry::get('ControlledVocabEntrySettingsDAO')->read(array(
            'controlled_vocab_entry_id' => $stewartInterests->get(0)->getData(
                'controlled_vocab_entry_id'
            )
        ))->get(0);
        $entry1 = Registry::get('ControlledVocabEntrySettingsDAO')->read(array(
            'controlled_vocab_entry_id' => $stewartInterests->get(1)->getData(
                'controlled_vocab_entry_id'
            )
        ))->get(0);
        $entry2 = Registry::get('ControlledVocabEntrySettingsDAO')->read(array(
            'controlled_vocab_entry_id' => $stewartInterests->get(2)->getData(
                'controlled_vocab_entry_id'
            )
        ))->get(0);

        $interests = array(
            $entry0->getData('setting_value'),
            $entry1->getData('setting_value'),
            $entry2->getData('setting_value'),
        );

        $equals = Registry::get('ArrayHandler')->equals(
            array(
                'Intergalatic security',
                'deep space',
                'science',
            ),
            $interests
        );


        $this->assertSame(
            '1-4-1-greenlantern2-3-1',
            implode('-', array(
                $imported,
                $users->length(),
                $search->length(),
                $user->getData('username'),
                $stewartInterests->length(),
                $equals,
            ))
        );

        if ($imported)
            $this->importedUsers++;
    }

    /**
     * @depends testCanImportUserIronMan
     */
    public function testCanGetIronManUserSettingsFromDatabase()
    {
        $ironman = Registry::get('UsersDAO')->read(array(
            'username' => 'ironman',
        ))->get(0);

        //$this->assertSame('Stark', $ironman->getData('last_name'));

        $settings = Registry::get('UserHandler')->getUserSettings($ironman);

        $this->assertSame(4, $settings->length());
    }

    /**
     * @depends testCanImportUserIronMan
     */
    public function testCanGetIronManUserRolesInTheTestJournal()
    {
        $journal = Registry::get('JournalsDAO')->read(array(
            'path' => 'test_journal',
        ))->get(0);

        $ironman = Registry::get('UsersDAO')->read(array(
            'username' => 'ironman',
        ))->get(0);

        $roles = Registry::get('UserHandler')->getUserRoles($ironman, $journal);

        $expected = array(
            array(
                '__tablename_' => 'roles',
                'user_id' => $ironman->getId(), 
                'journal_id' => $journal->getId(), 
                'role_id' => '16'
            ),
            array(
                '__tablename_' => 'roles',
                'user_id' => $ironman->getId(), 
                'journal_id' => $journal->getId(), 
                'role_id' => '256'
            ),
            array(
                '__tablename_' => 'roles',
                'user_id' => $ironman->getId(), 
                'journal_id' => $journal->getId(), 
                'role_id' => '4096'
            ),
        );

        $this->assertTrue(Registry::get('ArrayHandler')->areEquivalent(
            $expected,
            $roles->toArray()
        ));

        /*$this->assertEquals(
            $expected,
            $roles->toArray()
        );*/
    }

    /**
     * @depends testCanImportUserIronMan
     */
    public function testGetIronManUserInterestsFromDatabase()
    {
        $ironman = Registry::get('UsersDAO')->read(array(
            'username' => 'ironman',
        ))->get(0);

        //$this->assertSame('Stark', $ironman->getData('last_name'));

        $interests = Registry::get('UserHandler')->getUserInterests($ironman);

        //$this->assertSame(3, $interests->length());

        $userInts = array();

        for ($i = 0; $i < $interests->length(); $i++) {
            $userInts[] = $interests->get($i)->get('controlled_vocab_entries')
                      ->get(0)->get('settings')
                      ->get(0)->getData('setting_value');
        }

        $this->assertTrue(Registry::get('ArrayHandler')->equals(
            $userInts,
            array(
                'high tech',
                'science',
                'parties',
            )
        ));
    }

    /**
     * @depends testCanImportUserIronMan
     */
    public function testCanGetUsersFromTestJournal()
    {
        $journal = Registry::get('JournalsDAO')->read(array(
            'path' => 'test_journal',
        ))->get(0);

        Registry::get('UserHandler')->exportUsersFromJournal($journal);

        $list = Registry::get('FileSystemManager')->listdir(
            Registry::get('EntityHandler')->getEntityDataDir('users')
        );

        $ah = Registry::get('ArrayHandler');
        $eh = Registry::get('EntityHandler');
        $jh = Registry::get('JsonHandler');

        // 1 - Tony Stark
        // 2 - Bruce Wayne
        // 3 - Hal Jordan
        // 4 - John Stewart

        $ironmanExported = $jh->createFromFile($list[0]);
        $ironman = $this->createIronMan();
        $ironman->get('roles')->forEachValue(function($role) {
            $role->set(
                'journal_id',
                Registry::get('DataMapper')->getMapping(
                    'journals',
                    $role->get('journal_id')->getValue()
                )
            );
            $role->set(
                'user_id',
                Registry::get('DataMapper')->getMapping(
                    'users',
                    $role->get('user_id')->getValue()
                )
            );
        });
        $ironman->get('settings')->forEachValue(function($setting) {
            $setting->set(
                'user_id',
                Registry::get('DataMapper')->getMapping(
                    'users',
                    $setting->get('user_id')->getValue()
                )
            );
            $setting->set('assoc_type', 0);
            $setting->set('assoc_id', 0);
        });

        $this->assertEquals(
            '4-1-1',
            implode('-', array(
                count($list),
                $ah->areEquivalent(
                    $ironman->get('settings')->toArray(),
                    $ironmanExported->get('settings')->toArray()
                ),
                $ah->areEquivalent(
                    $ironman->get('roles')->toArray(),
                    $ironmanExported->get('roles')->toArray()
                ),
            ))
        );
    }
}
