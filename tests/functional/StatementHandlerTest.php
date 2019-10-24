<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\StatementHandler;
use BeAmado\OjsMigrator\Db\ConnectionManager;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\WorkWithOjsDir;

class StatementHandlerTest extends TestCase
{
    use WorkWithOjsDir;

    protected function setUp() : void
    {
        if (!Registry::hasKey('OjsDir'))
            Registry::set('OjsDir', $this->getOjsPublicHtmlDir());

        if (!$this->ojsDirExists())
            $this->createSandbox();
            $this->untarOjsDir();
    }

    public static function tearDownAfterClass() : void
    {
        Registry::clear();
    }

    public function testCanCreateAStatement()
    {
        if (
            !array_search('pdo_sqlite', get_loaded_extensions()) &&
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
