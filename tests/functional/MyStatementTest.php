<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\MyStatement;
use BeAmado\OjsMigrator\Db\ConnectionManager;
use BeAmado\OjsMigrator\Registry;

class MyStatementTest extends TestCase
{
    public static function setUpBeforeClass() : void
    {
        if (!Registry::hasKey('ConnectionManager'))
            Registry::set('ConnectionManager', new ConnectionManager());
    }

    public function testCreateStatement()
    {
        $query = 'SELECT * FROM users WHERE name = "Edil" AND level = "superstar"';
        $stmt = new MyStatement($query);

        $this->assertInstanceOf(
            MyStatement::class,
            $stmt
        );
    }
}
