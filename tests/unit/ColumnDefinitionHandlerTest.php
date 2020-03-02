<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\ColumnDefinitionHandler;
use BeAmado\OjsMigrator\Registry;

// traits
use BeAmado\OjsMigrator\Test\WorkWithXmlSchema;

class ColumnDefinitionHandlerTest extends TestCase
{
    use WorkWithXmlSchema;

    public function testCanSeeThatJournalIdIsAColumn()
    {
        $journalsRaw = Registry::get('MemoryManager')->create(
            $this->journalsSchemaRawArray()
        );

        $journalId = $journalsRaw->get('children')->get(0);

        $this->assertTrue(
            $journalId->get('attributes')
                      ->get('name')
                      ->getValue() === 'journal_id' &&
            Registry::get('ColumnDefinitionHandler')->isColumn($journalId)
        );
    }
}
