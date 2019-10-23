<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\StatementHandler;
use BeAmado\OjsMigrator\Db\ConnectionManager;
use BeAmado\OjsMigrator\Registry;

class StatementHandlerTest extends TestCase
{
    public function testCanCreateAStatement()
    {
        if (
            !array_search('pdo_sqlite', get_loaded_extensions()) ||
            !array_search('pdo_mysql', get_loaded_extensions())
        ) {
            $this->markTestSkipped('None of the database drivers available');
        }

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
