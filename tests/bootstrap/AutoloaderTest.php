<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\Autoloader;

// interfaces
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;


class AutoloaderTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends Autoloader {
            use TestStub;
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

    public function testRegisterTheAutoload()
    {
        $this->assertTrue(
            (new \BeAmado\OjsMigrator\Util\Autoloader())->registerAutoload()
        );
    }

    public function testCanIncludeMyObject()
    {
        $this->assertTrue(
            $this->getStub()->callMethod(
                'includeElement', 
                $this->getStub()->callMethod(
                    'formFullpath', 
                    array(
                        'classname' => 'MyObject', 
                        'args' => array('classes', 'core')
                    )
                )
            )
        );
    }

    public function testCanLoadClassMyObject()
    {
        $this->assertTrue(
            $this->getStub()->callMethod(
                'loadClass',
                'MyObject'
            )
        );
    }

    public function testCanLoadInterfaceMyIterable()
    {
        $this->assertTrue(
            $this->getStub()->callMethod(
                'loadInterface',
                'MyIterable'
            )
        );
    }
}
