<?php

namespace BeAmado\OjsMigrator;

use \PHPUnit\Framework\TestCase;

abstract class FunctionalTest extends TestCase
{
    public static function setUpbeforeClass() : void
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
}
