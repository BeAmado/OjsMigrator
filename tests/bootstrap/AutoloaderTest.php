<?php

use PHPUnit\Framework\TestCase;

class AutoloaderTest extends TestCase
{
    protected function setUp() : void
    {
        require_once(dirname(__FILE__) . '/../TestStub.php');
        $this->autoloaderStub = new class extends \BeAmado\OjsMigrator\Util\Autoloader {
            use \BeAmado\OjsMigrator\TestStub;
        };
    }

    public function testFullpathIsWellFormed()
    {
        $this->assertEquals(
            \BeAmado\OjsMigrator\LIB_DIR . '/lalala/lelele/Lilili.php',
            $this->autoloaderStub->callMethod(
                'formFullpath', 
                array(
                    'classname' => 'Lilili',
                    'args' => array('lalala', 'lelele')
                )
            )
        );
    }

    public function testNameIsAnInterface()
    {
        $this->assertTrue(
            $this->autoloaderStub->callMethod(
                'nameIsInterface', 
                'labareda/do/bpde/Interfaces/chaparres'
            )
        );
    }

    public function testNameIsNotAnInterface()
    {
        $this->assertFalse(
            $this->autoloaderStub->callMethod(
                'nameIsInterface', 
                'labareda/do/bpde/Interes/chaparres'
            )
        );
    }

    public function testNameIsATrait()
    {
        $this->assertTrue(
            $this->autoloaderStub->callMethod(
                'nameIsTrait', 
                'labareda/do/bpde/Traits/chaparres'
            )
        );
    }

    public function testNameIsNotATrait()
    {
        $this->assertFalse(
            $this->autoloaderStub->callMethod(
                'nameIsTrait', 
                'labareda/do/bps/chaparres'
            )
        );
    }

    public function testRegisterTheAutoload()
    {
        $this->assertTrue(
            (new \BeAmado\OjsMigrator\Util\Autoloader())->registerAutoload()
        );
    }

    public function testCanIncludeMongoose()
    {
        $this->assertTrue(
            $this->autoloaderStub->callMethod(
                'includeElement', 
                $this->autoloaderStub->callMethod(
                    'formFullpath', 
                    array(
                        'classname' => 'Mongoose', 
                        'args' => array('classes', 'core')
                    )
                )
            )
        );
    }

    public function testCanLoadClassMongoose()
    {
        $this->assertTrue(
            $this->autoloaderStub->callMethod(
                'loadClass',
                'Mongoose'
            )
        );
    }
}
