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

    public static function setUpBeforeClass($args = array(
        'clearRegistry' => true,
    )) : void {
        if (!\array_key_exists('clearRegistry', $args))
            $args['clearRegistry'] = true;

        if ($args['clearRegistry'])
            Registry::clear();
        (new OjsScenarioHandler())->setUpStage($args);
    }

    public static function tearDownAfterClass($args = array()) : void
    {
        //(new OjsScenarioHandler())->removeSandbox();
        (new OjsScenarioHandler())->tearDownStage($args);
        Registry::clear();
    }

    protected function areEqual($a, $b)
    {
        return $a == $b;
    }
}
