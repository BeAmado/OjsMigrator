<?php

use PHPUnit\Framework\TestCase;

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

}
