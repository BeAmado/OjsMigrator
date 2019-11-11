<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Db\DAO;

class DAOTest extends FunctionalTest
{
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
        ));
        
        $updatedRows = Registry::get('UsersDao')->update($conditions);

        $userAfter = Registry::get('UsersDAO')->read(array(
            'username' => 'satch'
        ));

        $this->assertSame(1, $updatedRows);

        $this->assertTrue(
            $updatedRows === 1 &&
            !Registry::get('EntityHandler')->areEqual(
                $userBefore,
                $userAfter
            )
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

        $this->assertTrue(
            $users->length() === 3 &&
            $deletions === 0
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
}
