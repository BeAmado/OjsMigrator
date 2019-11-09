<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Db\MyStatement;
use BeAmado\OjsMigrator\Db\ConnectionManager;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\WorkWithOjsDir;

class MyStatementTest extends FunctionalTest
{
    use WorkWithOjsDir;

    public function testCreateStatement()
    {
        if (
            !array_search('pdo_sqlite', get_loaded_extensions()) &&
            !array_search('pdo_mysql', get_loaded_extensions())
        ) {
            $this->markTestSkipped('None of the database drivers available');
        }

        Registry::get('DbHandler')->createTableIfNotExists('users');
        $query = 'SELECT * FROM users '
            . 'WHERE first_name = "Bernardo" AND last_name = "Amado"';
        $stmt = new MyStatement($query);

        $this->assertInstanceOf(
            MyStatement::class,
            $stmt
        );
    }
}
