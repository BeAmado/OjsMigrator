<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\IndexDefinitionHandler;
use BeAmado\OjsMigrator\Registry;

// traits
use BeAmado\OjsMigrator\Test\WorkWithXmlSchema;

class IndexDefinitionHandlerTest extends TestCase
{
    use WorkWithXmlSchema;

    public function testCanSeeThatJournalSettingsJournalIdIsAnIndex()
    {
        $journalSettingsRaw = Registry::get('MemoryManager')->create(
            $this->journalSettingsSchemaRawArray()
        );

        $index = $journalSettingsRaw->get('children')->get(6);

        $this->assertTrue(
            Registry::get('IndexDefinitionHandler')
                    ->getIndexName($index) === 'journal_settings_journal_id' &&
            Registry::get('IndexDefinitionHandler')->isIndex($index)
        );
    }
}
