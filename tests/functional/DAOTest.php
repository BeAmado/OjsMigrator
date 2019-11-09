<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Db\DAO;

class DAOTest extends FunctionalTest
{
    public function testCreateUser()
    {
        Registry::get('DbHandler')->createTable('users');

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
            $user['user_id']== 1
        );


    }
}
