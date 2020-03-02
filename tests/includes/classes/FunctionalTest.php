<?php

namespace BeAmado\OjsMigrator\Test;
use \BeAmado\OjsMigrator\Registry;

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

    public static function setUpBeforeClass($args = array()) : void
    {
        Registry::clear();
        (new OjsScenarioHandler())->setUpStage($args);
        (new DataMappingHandler())->setUpDataMappingStage();
    }

    public static function tearDownAfterClass($args = array()) : void
    {
        //(new OjsScenarioHandler())->removeSandbox();
        (new DataMappingHandler())->tearDownDataMappingStage();
        (new OjsScenarioHandler())->tearDownStage($args);
        Registry::clear();
    }

    protected function areEqual($a, $b)
    {
        return $a == $b;
    }
}
