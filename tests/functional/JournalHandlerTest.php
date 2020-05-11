<?php

use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Test\FixtureHandler;
use BeAmado\OjsMigrator\Test\JournalMock;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Entity\JournalHandler;

class JournalHandlerTest extends FunctionalTest
{
    public static function setUpBeforeClass($args = []) : void
    {
        parent::setUpBeforeClass();
        (new FixtureHandler())->createTablesForEntities('journals');
        Registry::set('entitiesDirBkp', Registry::get('entitiesDir'));
    }

    public function tearDown() : void
    {
        if (Registry::get('entitiesDir') === Registry::get('entitiesDirBkp'))
            return;

        Registry::set(
            'entitiesDir',
            Registry::get('entitiesDirBkp')
        );
    }

    protected function createTestJournal()
    {
        return (new JournalMock())->getTestJournal();
    }

    public function testCanGetTheMockedTestJournal()
    {
        $this->assertSame(
            'test_journal',
            $this->createTestJournal()->getData('path')
        );
    }

    public function testCanImportTheTestJournal()
    {
        $journalsBefore = Registry::get('JournalsDAO')->read();
        $journal = $this->createTestJournal();
        $imported = Registry::get('JournalHandler')->import($journal);

        $this->assertSame(
            '1-1-1-1-4',
            implode('-', [
                (int) $imported,
                (int) is_null($journalsBefore),
                Registry::get('JournalsDAO')->read()->length(),
                Registry::get('PluginSettingsDAO')->read()->length(),
                Registry::get('JournalSettingsDAO')->read()->length(),
            ])
        );
    }

    public function testCanCorrectlyGetTheJournalFilenameFromEntitiesDir()
    {
        Registry::set(
            'entitiesDir',
            Registry::get('FileSystemManager')->formPathFromBaseDir([
                'tests',
                '_data',
                'sandbox',
                'www.test.com',
                'OjsMigrator',
                'entities',
            ])
        );

        $journal = Registry::get('JournalHandler')->create([
            'journal_id' => 222,
            'path' => 'mammamia',
        ]);

        Registry::get('JournalHandler')->dumpEntity($journal);

        $this->assertEquals(
            $journal->getId(),
            Registry::get('JournalHandler')->getJournalIdFromEntitiesDir()
        );
    }

    /**
     * @depends testCanImportTheTestJournal
     */
    public function testCanExportTheTestJournal()
    {
        $journal = $this->createTestJournal();
        $journalId = Registry::get('DataMapper')->getMapping(
            'journals',
            $journal->getId()
        );

        $handler = Registry::get('JournalHandler');

        $fileExistedBefore = Registry::get('FileSystemManager')->fileExists(
            $handler->getJournalFilenameInEntitiesDir()
        );

        Registry::get('JournalHandler')->export($journalId);
        
        $fileExistsAfter = Registry::get('FileSystemManager')->fileExists(
            $handler->getJournalFilenameInEntitiesDir()
        );

        $this->assertSame(
            '0;1',
            implode(';', [
                (int) $fileExistedBefore,
                (int) $fileExistsAfter,
            ])
        );
    }

    /**
     * @depends testCanExportTheTestJournal
     */
    public function testFixesTheBrokenSettingValueSerializationBeforeExport()
    {
        $journal = $this->createTestJournal();
        $journalId = Registry::get('DataMapper')->getMapping(
            'journals',
            $journal->getId()
        );

        $settings = Registry::get('JournalSettingsDAO')->read([
            'journal_id' => $journalId
        ]);

        $broken = [];
        for ($i = 0; $i < $settings->length(); $i++) {
            $name = $settings->get($i)->getData('setting_name');
            if (\in_array($name, ['madre', 'albums',]))
                $broken[$name] = $settings->get($i)->getData('setting_value');
        }

        $expSettings = Registry::get('JsonHandler')->createFromFile(
            Registry::get('JournalHandler')->getJournalFilenameInEntitiesDir()
        )->get('settings');

        $good = [];
        for ($i = 0; $i < $expSettings->length(); $i++) {
            $name = $expSettings->get($i)->get('setting_name')->getValue();
            if (\in_array($name, ['madre', 'albums',]))
                $good[$name] = $expSettings->get($i)
                                           ->get('setting_value')->getValue();
        }

        $merged = array(
            $broken['madre'],
            $broken['albums'],
            $good['madre'],
            $good['albums']
        );

        $this->assertSame(
            '||1|1',
            implode('|', array_map(
                array(Registry::get('SerialDataHandler'), 'serializationIsOk'),
                $merged
            ))
        );
    }
}
