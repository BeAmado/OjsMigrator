<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Entity\UserHandler;
use BeAmado\OjsMigrator\UserMock;

// interfaces 
use BeAmado\OjsMigrator\StubInterface;

// traits
use BeAmado\OjsMigrator\TestStub;

class UserHandlerTest extends FunctionalTest implements StubInterface
{
    public function __construct()
    {
        parent::__construct();
        $this->userMock = new UserMock();

    }

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();
        foreach (array(
            'users',
            'user_settings',
            'user_interests',
            'controlled_vocabs',
            'controlled_vocab_entries',
            'controlled_vocab_entry_settings',
            'roles',
            'journals',
        ) as $table) {
            Registry::get('DbHandler')->createTableIfNotExists($table);
        }

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

        $this->assertSame(
            'Stark-Banner-Bruce-Clint-Jordan',
            implode('-', array(
                $ironman->get('last_name')->getValue(),
                $hulk->get('last_name')->getValue(),
                $batman->get('first_name')->getValue(),
                $hawkeye->get('first_name')->getValue(),
                $greenlantern->get('last_name')->getValue(),
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
            'importInterest',
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

        $this->assertTrue(
            $imported &&
            Registry::get('DataMapper')->isMapped(
                'controlled_vocab_entries',
                $interest->get('controlled_vocab_entry_id')->getValue()
            ) &&
            Registry::get('DataMapper')->isMapped(
                'controlled_vocabs',
                $interest->get('controlled_vocab_entries')
                         ->get(0)
                         ->get('controlled_vocab_id')->getValue()
            ) &&
            $entrySettings->length() === 1 &&
            $entrySettings->get(0)->getData('setting_value') === 'science'
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

        /*var_dump(array(
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

        var_dump($roles);
        var_dump(Registry::get('selectRolesStmt'));*/

        $this->assertSame(
            '1-1', 
            implode('-', array(
                $imported,
                $roles->length(),
            ))
        );
    }
}
