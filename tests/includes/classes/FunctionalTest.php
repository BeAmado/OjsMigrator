<?php

namespace BeAmado\OjsMigrator;

use \PHPUnit\Framework\TestCase;

abstract class FunctionalTest extends TestCase
{
    protected static function useTables($tables = array())
    {
        if (!is_array($tables))
            return;

        foreach ($tables as $table) {
            Registry::get('DbHandler')->createTableIfNotExists($table);
        }
    }

    protected static function setUpTestJournal()
    {
        Registry::get('DbHandler')->createTableIfNotExists('journals');
        Registry::get('EntityHandler')->createOrUpdateInDatabase(
            (new JournalMock())->getTestJournal()
        );
    }

    public static function setUpBeforeClass() : void
    {
        Registry::clear();
        (new OjsScenarioTester())->setUpStage();
        (new DataMappingTester())->setUpDataMappingStage();
    }

    public static function tearDownAfterClass() : void
    {
        //(new OjsScenarioTester())->removeSandbox();
        (new DataMappingTester())->tearDownDataMappingStage();
        (new OjsScenarioTester())->tearDownStage();
        Registry::clear();
    }

    protected function areEqual($a, $b)
    {
        return $a == $b;
    }
}
