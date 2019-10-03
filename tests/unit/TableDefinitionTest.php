<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\TableDefinition;
use BeAmado\OjsMigrator\StubInterface;
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\WorkWithXmlSchema;

class TableDefinitionTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends TableDefinition {
            use TestStub;
            use WorkWithXmlSchema;
        };
    }

    public function testCreateOjsJournalTableDefinition()
    {
        $def = new TableDefinition(
            $this->getStub()->schemaArray()['journals']
        );

        $this->assertTrue(
            $def->hasColumn('journal_id') &&
            $def->isPrimaryKey('journal_id') &&
            !$def->isNullable('journal_id')
        );
    }
}
