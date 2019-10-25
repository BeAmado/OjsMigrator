<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\OjsScenarioTester;
use BeAmado\OjsMigrator\ConfigPreprocessor;
use BeAmado\OjsMigrator\Registry;

class BootstrapTest extends TestCase
{
    public function testBootstrapFileExists()
    {
        $this->assertTrue(file_exists(dirname(__FILE__) . '/../../includes/bootstrap.php'));
    }

    public function testWeHaveTestStubTrait()
    {
        $this->assertTrue(trait_exists('BeAmado\OjsMigrator\TestStub'));
    }

    public function testWeHaveAutoloaderClass()
    {
        $this->assertTrue(class_exists('BeAmado\OjsMigrator\Util\Autoloader'));
    }

    public function testCreateConfigFile()
    {
        (new OjsScenarioTester())->prepareStage();

        $this->assertFileExists(
            (new OjsScenarioTester())->getOjsConfigFile()
        );
    }

}
