<?php

use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Db\Sqlite\SqliteDAO as DAO;

// traits
use \BeAmado\OjsMigrator\Test\TestStub;

class SqliteDAOTest extends FunctionalTest
{
    public static function setUpBeforeClass($args = array(
        'createTables' => array(
            'users',
            'user_settings',
            'sections',
            'submission_files',
        ),
    )) : void {

        $dbDvrFile = Registry::get('FileSystemManager')->formPathFromBaseDir([
            'tests',
            'dbdriver',
        ]);

        Registry::set(
            'dbdriver',
            Registry::get('FileHandler')->read($dbDvrFile)
        );

        Registry::get('FileHandler')->write(
            $dbDvrFile,
            'sqlite'
        );

        parent::setUpBeforeClass($args);
    }

    public static function tearDownAfterClass($args = array()) : void
    {
        $dbDvrFile = Registry::get('FileSystemManager')->formPathFromBaseDir([
            'tests',
            'dbdriver',
        ]);

        Registry::get('FileHandler')->write(
            $dbDvrFile,
            Registry::get('dbdriver')
        );
        parent::tearDownAfterClass();
    }

    public function testTheDaoIsSqlite()
    {
        $this->assertInstanceOf(
            \BeAmado\OjsMigrator\Db\Sqlite\SqliteDAO::class,
            Registry::get('UsersDAO')
        );
    }

    public function testCreateUser()
    {
        Registry::get('DbHandler')->createTableIfNotExists('users');

        $user = (new DAO('users'))->create(
            Registry::get('EntityHandler')->create(
                'users',
                array(
                    'username' => 'be',
                    'first_name' => 'Bernardo',
                    'last_name' => 'Amado',
                    'email' => 'bernardo@example.com',
                    'password' => 'monpass',
                )
            )
        );

        $this->assertEquals(
            1,
            $user->getData('user_id')
        );
    }

    /**
     * @depends testCreateUser
     */
    public function testCreateUserPassingAnArray()
    {
        $user = Registry::get('UsersDAO')->create(array(
            'username' => 'satch',
            'first_name' => 'Joe',
            'last_name' => 'Satriani',
            'email' => 'joe@thetop.com',
            'password' => 'I like the rain',
        ));

        $this->assertEquals(
            2,
            $user->getData('user_id')
        );
    }

    /**
     * @depends testCreateUserPassingAnArray
     */
    public function testSelectAllUsers()
    {
        $usersToInsert = array(
            array(
                'username' => '007',
                'first_name' => 'James',
                'last_name' => 'Bond',
                'email' => 'bond@mi6.gov',
                'password' => 'you know my name',
            ),
            array(
                'username' => 'Indiana Jones',
                'first_name' => 'Henry',
                'middle_name' => 'Jones',
                'last_name' => 'Jr.',
                'email' => 'imnot@thelibrary.ancient',
                'password' => 'Alexandreta',
            )
        );

        foreach ($usersToInsert as $user) {
            Registry::get('UsersDAO')->create($user);
        }

        $users = Registry::get('UsersDAO')->read();

        $this->assertSame(
            4,
            count($users->listKeys())
        );
    }

    /**
     * @depends testCreateUser
     */
    public function testSelectUserByUsername()
    {
        $users = Registry::get('UsersDAO')->read(array('username' => 'be'));

        $this->assertTrue(
            $users->length() === 1 &&
            $users->get(0)->getData('first_name') === 'Bernardo'
        );
    }

    /**
     * @depends testSelectUserByUsername
     */
    public function testUpdateUserPassingEntity()
    {
        $user = Registry::get('UsersDAO')->read(array('username' => 'be'))
                                         ->get(0);

        $userBefore = $user->cloneInstance();

        $user->set('initials', 'BA');

        $rowsUpdated = Registry::get('UsersDAO')->update($user);

        $userAfter = Registry::get('UsersDAO')->read(array('username' => 'be'))
                                              ->get(0);

        $this->assertTrue(
            $rowsUpdated === 1 &&
            !Registry::get('EntityHandler')->areEqual(
                $userBefore, 
                $userAfter
            ) &&
            $userBefore->getData('initials') === null &&
            $userAfter->getData('initials') === 'BA'
        );
    }

    /**
     * @depends testSelectUserByUsername
     * @depends testCreateUserPassingAnArray
     */
    public function testUpdateUserPassingConditions()
    {
        $conditions = array(
            'where' => array(
                'username' => 'satch',
            ),
            'set' => array(
                'initials' => 'JS',
                'country' => 'Outer Space',
            ),
        );

        $userBefore = Registry::get('UsersDAO')->read(array(
            'username' => 'satch'
        ))->get(0);
        
        $updatedRows = Registry::get('UsersDao')->update($conditions);

        $userAfter = Registry::get('UsersDAO')->read(array(
            'username' => 'satch'
        ))->get(0);

        $this->assertTrue(
            $updatedRows === 1 &&
            !Registry::get('EntityHandler')->areEqual(
                $userBefore,
                $userAfter
            ) &&
            $userBefore->getData('initials') === null &&
            $userAfter->getData('initials') === 'JS'
        );
    }

    /**
     * @depends testSelectAllUsers
     * @depends testSelectUserByUsername
     */
    public function testDeleteUserByUsername()
    {
        $deletions = Registry::get('UsersDAO')->delete(array(
            'username' => 'be'
        ));

        $users = Registry::get('UsersDAO')->read();

        $this->assertTrue(
            $users->length() === 3 &&
            $deletions === 1
        );
    }

    /**
     * @depends testDeleteUserByUsername
     */
    public function testCannotDeleteAllUsers()
    {
        $deletions = Registry::get('UsersDAO')->delete();

        $users = Registry::get('UsersDAO')->read();

        /*$this->assertTrue(
            $users->length() === 3 &&
            $deletions === 0
        );*/
        $this->assertSame(
            '3-0',
            implode('-', array(
                $users->length(),
                (int) $deletions
            ))
        );
    }

    public function testDeleteUserPassingEntity()
    {
        $user = Registry::get('UsersDAO')->create(array(
            'username' => 'mastercroc',
            'first_name' => 'Jean-Claude',
            'last_name' => 'Van Damme',
            'email' => 'master@martialarts.com',
            'password' => 'full split kick',
        ));

        $usersBeforeDeletion = Registry::get('UsersDAO')->read();

        $deletions = Registry::get('UsersDAO')->delete($user);

        $usersAfterDeletion = Registry::get('UsersDAO')->read();

        $this->assertTrue(
            $usersBeforeDeletion->length() === 4 &&
            $usersBeforeDeletion->get(-1)
                                ->getData('first_name') === 'Jean-Claude' &&
            $deletions === 1 &&
            $usersAfterDeletion->length() === 3
        );
    }

    public function testCanFormSectionJsonFilename()
    {
        $section = Registry::get('EntityHandler')->create('sections', array(
            'section_id' => 14,
            'journal_id' => 5,
        ));

        $stub = new class('sections') extends DAO {
            use TestStub;
        };

        $filename = $stub->callMethod(
            'formJsonFilename',
            $section
        );

        $expected = Registry::get('EntitiesDir')
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'sections'
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . '14.json';

        $this->assertSame($expected, $filename);
    }

    protected function insertSections()
    {
        Registry::get('DbHandler')->createTableIfNotExists('sections');

        for ($i = 0; $i < 5; $i++) {
            Registry::get('SectionsDAO')->create(array(
                'journal_id' => 12,
                'review_form_id' => rand(1, 10),
            ));
        }

        for ($i = 0; $i < 5; $i++) {
            Registry::get('SectionsDAO')->create(array(
                'journal_id' => 41,
                'review_form_id' => rand(1, 3),
            ));
        }
    }

    public function testDumpSectionsFromJournal41ToJson()
    {
        $this->insertSections();
        Registry::get('SectionsDAO')->dumpToJson(array(
            'journal_id' => 41
        ));

        $this->assertSame(
            5,
            count(Registry::get('FileSystemManager')->listdir(
                Registry::get('EntityHandler')->getEntityDataDir('sections')
            ))
        );
    }

    public function testCanSelectUserPassingEntity()
    {
        Registry::get('DbHandler')->createTableIfNotExists('users');
        Registry::get('DbHandler')->createTableIfNotExists('user_settings');

        $user = Registry::get('UsersDAO')->create(array(
            'username' => 'agent',
            'first_name' => 'Ethan',
            'last_name' => 'Hunt',
            'email' => 'hunt@imf.gov',
            'password' => 'impossible',
        ));

        $users = Registry::get('UsersDAO')->read();

        $candidates = Registry::get('UsersDAO')->read($user);

        $this->assertSame(
            '1-1-Ethan-Hunt',
            implode('-', array(
                $users->length() > 1,
                $candidates->length(),
                $candidates->get(0)->getData('first_name'),
                $candidates->get(0)->getData('last_name'),
            ))
        );
    }

    public function testCanGetTheLastIdForTheSubmissionFile()
    {
        $dao = Registry::get('SubmissionHandler')->getDAO('files');
        $firstId = $dao->formManualIncrementedId();
        $file = $dao->create(Registry::get('SubmissionFileHandler')->create([
            'file_id' => 372,
            'revision' => 3,
        ]));

        $this->assertSame(
            '1-1-2',
            implode('-', [
                $firstId,
                $file->getId(),
                $dao->formManualIncrementedId(),
            ])
        );
    }
}
