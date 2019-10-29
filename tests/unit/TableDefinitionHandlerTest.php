<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\TableDefinitionHandler;
use BeAmado\OjsMigrator\WorkWithXmlSchema;
use BeAmado\OjsMigrator\Registry;

class TableDefinitionHandlerTest extends TestCase
{
    use WorkWithXmlSchema;

    public function testCheckThatJournalsIsATable()
    {
        $rawJournals = Registry::get('MemoryManager')->create(
            $this->journalsSchemaRawArray()
        );

        $this->assertTrue(
            Registry::get('TableDefinitionHandler')->isTable($rawJournals)
        );

    }
}
