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

        $this->assertTrue(
            $user['user_id'] == 1
        );
    }

    /**
     * @depends testCreateUser
     */
    public function testSelectUserByUsername()
    {
        $users = Registry::get('UsersDAO')->read(array('username' => 'be'));

        $this->assertSame(
            'Bernardo',
            $users->get(0)->getData('first_name')
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

        $this->assertTrue($user['user_id'] == 2);
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
}
