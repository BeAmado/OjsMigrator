<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Application;
use BeAmado\OjsMigrator\Registry;

///////// interfaces /////////////////
use BeAmado\OjsMigrator\StubInterface;

/////////// traits ///////////////////
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\WorkWithFiles;

class ApplicationTest extends TestCase implements StubInterface
{
    use WorkWithFiles;

    public static function setUpBeforeClass() : void
    {
        $app = new class extends Application {
            use TestStub;
        };

        Registry::clear();
    }

    public static function tearDownAfterClass() : void
    {
        $app = new class extends Application {
            use TestStub;
        };

        if (Registry::hasKey('SchemaDir'))
            $app->callMethod('removeSchema');

        Registry::get('FileSystemManager')->removeWholeDir(
            Registry::get('FileSystemManager')->formPathFromBaseDir(array(
                'tests',
                '_data',
                'sandbox',
            ))
        );
        //$app->callMethod('finish');

    }

    public function getStub()
    {
        return new class extends Application {
            use TestStub;
        };
    }

    public function testCanLoadManagers()
    {
        Registry::clear();
        $this->getStub()->callMethod('loadManagers');
        $this->assertTrue(
            // managers
            Registry::hasKey('ArchiveManager') &&
            Registry::hasKey('FileSystemManager') &&
            Registry::hasKey('IoManager') &&
            Registry::hasKey('MemoryManager')
        );
    }

    public function testCanLoadHandlers()
    {
        Registry::clear();
        $this->getStub()->callMethod('loadHandlers');
        $this->assertTrue(
            // handlers
            Registry::hasKey('ConfigHandler') &&
            Registry::hasKey('DbHandler') &&
            Registry::hasKey('FileHandler') &&
            Registry::hasKey('JsonHandler') &&
            Registry::hasKey('SchemaHandler') &&
            Registry::hasKey('XmlHandler')
        );
    }

    public function testSetSpecificOjsDir()
    {
        Registry::clear();
        $this->getStub()->callMethod(
            'setOjsDir',
            '/path/to/ojs/dir'
        );

        $this->assertSame(
            '/path/to/ojs/dir',
            Registry::get('OjsDir')
        );
    }

    public function testSetOjsDirByDefaultOption()
    {
        Registry::clear();
        $this->getStub()->callMethod('setOjsDir');

        $this->assertSame(
            dirname(\BeAmado\OjsMigrator\BASE_DIR),
            Registry::get('OjsDir')
        );
    }

}
