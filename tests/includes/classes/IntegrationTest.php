<?php

namespace BeAmado\OjsMigrator\Test;
use \PHPUnit\Framework\TestCase;
use \BeAmado\OjsMigrator\Registry;

abstract class IntegrationTest extends TestCase
{
    public static function setUpBeforeClass() : void
    {
        Registry::clear();
        (new OjsScenarioHandler())->setUpStage();
        (new FixtureHandler())->createTablesForEntities([
            'announcements',
            'groups',
            'issues',
            'journals',
            'review_forms',
            'sections',
            'submissions',
            'users',
        ]);
    }

    public static function tearDownAfterClass() : void
    {
        (new DataMappingHandler())->tearDownDataMappingStage();
        (new OjsScenarioHandler())->tearDownStage();
        Registry::clear();
    }
}
