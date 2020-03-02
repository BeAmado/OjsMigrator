<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Test\OjsScenarioHandler;
use BeAmado\OjsMigrator\Test\ConfigPreprocessor;
use BeAmado\OjsMigrator\Registry;

class BootstrapTest extends TestCase
{
    public static function setUpBeforeClass() : void
    {
        (new OjsScenarioHandler())->setUpStage();
    }

    public static function tearDownAfterClass() : void
    {
        (new OjsScenarioHandler())->tearDownStage();
    }

    public function testBootstrapFileExists()
    {
        $this->assertTrue(file_exists(dirname(__FILE__) . '/../../includes/bootstrap.php'));
    }

    public function testWeHaveTestStubTrait()
    {
        $this->assertTrue(trait_exists('BeAmado\OjsMigrator\Test\TestStub'));
    }

    public function testWeHaveAutoloaderClass()
    {
        $this->assertTrue(class_exists('BeAmado\OjsMigrator\Util\Autoloader'));
    }

    public function testCreateConfigFile()
    {
        $this->assertFileExists(
            (new OjsScenarioHandler())->getOjsConfigFile()
        );
    }

}
