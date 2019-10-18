<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\StatementHandler;
use BeAmado\OjsMigrator\Db\ConnectionManager;
use BeAmado\OjsMigrator\Registry;

class StatementHandlerTest extends TestCase
{
    public static function setUpBeforeClass() : void
    {
        if (!Registry::hasKey('ConnectionManager'))
            Registry::set('ConnectionManager', new ConnectionManager());
    }

    public function testCanCreateAStatement()
    {
        $this->assertInstanceOf(
            \BeAmado\OjsMigrator\Db\MyStatement::class,
            (new StatementHandler())->create(
                'CREATE TABLE person (
                    `id` integer, 
                    `name` varchar(50), 
                    primary key (`id`)
                )'
            )
        );
    }
}
