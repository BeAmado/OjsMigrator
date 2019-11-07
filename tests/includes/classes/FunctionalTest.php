<?php

namespace BeAmado\OjsMigrator;

use \PHPUnit\Framework\TestCase;

abstract class FunctionalTest extends TestCase
{
    public static function setUpbeforeClass() : void
    {
        Registry::clear();
        (new OjsScenarioTester())->setUpStage();
    }

    public static function tearDownAfterClass() : void
    {
        //(new OjsScenarioTester())->removeSandbox();
        (new OjsScenarioTester())->tearDownStage();
        Registry::clear();
    }
}
