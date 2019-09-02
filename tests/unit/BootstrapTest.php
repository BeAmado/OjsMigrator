<?php

use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public function testWeHaveTestStubTrait()
    {
        require_once(dirname(__FILE__) . '/TestStub.php');
        $this->assertTrue(\trait_exists('BeAmado\OjsMigrator\TestStub'));
    }

    public function testWeHaveAutoloaderClass()
    {
        $this->assertTrue(\class_exists('BeAmado\OjsMigrator\Util\Autoloader'));
    }

}
