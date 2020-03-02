<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\TableDefinition;

// traits
use BeAmado\OjsMigrator\Test\WorkWithXmlSchema;

class TableDefinitionTest extends TestCase
{
    use WorkWithXmlSchema;

    public function testCreateOjsJournalTableDefinition()
    {
        $def = new TableDefinition(
            $this->schemaArray()['journals']
        );

        $this->assertTrue(
            $def->hasColumn('journal_id') &&
            $def->getColumn('journal_id')->isPrimaryKey() &&
            $def->getColumn('journal_id')->isNullable() &&
            $def->getColumn('journal_id')->isAutoIncrement()
        );
    }

    public function testGetJournalsTablePrimaryKeys()
    {
        $def = new TableDefinition(
            $this->schemaArray()['journals']
        );

        $this->assertEquals(
            array('journal_id'),
            $def->getPrimaryKeys()
        );
    }

    public function testSetColumnDefinitionPassingOnlyTheDef()
    {
        $enabled = $this->schemaArray()['journals']['columns']['enabled'];
        $enabled['name'] = 'enabled';

        $tableDef = new TableDefinition();

        $tableDef->setColumnDefinition($enabled);

        $this->assertTrue(
            $tableDef->getColumn('enabled')->getDefaultValue() == 1
        );
    }
}
