<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\Autoloader;

class AutoloaderTest extends TestCase
{
    private function getStub()
    {
        require_once(dirname(__FILE__) . '/../TestStub.php');
        return new class extends Autoloader {
            use BeAmado\OjsMigrator\TestStub;
        };
    }

    public function testFullpathIsWellFormed()
    {
        $this->assertEquals(
            \BeAmado\OjsMigrator\LIB_DIR . '/lalala/lelele/Lilili.php',
            $this->getStub()->callMethod(
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
            $this->getStub()->callMethod(
                'nameIsInterface', 
                'labareda/do/bpde/Interfaces/chaparres'
            )
        );
    }

    public function testNameIsNotAnInterface()
    {
        $this->assertFalse(
            $this->getStub()->callMethod(
                'nameIsInterface', 
                'labareda/do/bpde/Interes/chaparres'
            )
        );
    }

    public function testNameIsATrait()
    {
        $this->assertTrue(
            $this->getStub()->callMethod(
                'nameIsTrait', 
                'labareda/do/bpde/Traits/chaparres'
            )
        );
    }

    public function testNameIsNotATrait()
    {
        $this->assertFalse(
            $this->getStub()->callMethod(
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
            $this->getStub()->callMethod(
                'includeElement', 
                $this->getStub()->callMethod(
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
            $this->getStub()->callMethod(
                'loadClass',
                'Mongoose'
            )
        );
    }
}
