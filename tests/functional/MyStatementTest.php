<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\MyStatement;
use BeAmado\OjsMigrator\Db\ConnectionManager;
use BeAmado\OjsMigrator\Registry;

class MyStatementTest extends TestCase
{
    public function testCreateStatement()
    {
        if (
            !array_search('pdo_sqlite', get_loaded_extensions()) ||
            !array_search('pdo_mysql', get_loaded_extensions())
        ) {
            $this->markTestSkipped('None of the database drivers available');
        }

        $query = 'SELECT * FROM users WHERE name = "Edil" AND level = "superstar"';
        $stmt = new MyStatement($query);

        $this->assertInstanceOf(
            MyStatement::class,
            $stmt
        );
    }
}
