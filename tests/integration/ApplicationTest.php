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

    public function testLoadSchema()
    {

        Registry::clear();
        $this->getStub()->callMethod('loadManagers');
        $this->getStub()->callMethod('loadHandlers');

        $this->getStub()->callMethod(
            'setOjsDir',
            $this->getDataDir() 
            . $this->sep() . 'sandbox' 
            . $this->sep() . 'ojs2' 
            . $this->sep() . 'public_html'
        );

        //extract the ojs2 dir to /tests/_data/sandbox
        Registry::get('ArchiveManager')->tar(
            'xzf',
            $this->getDataDir() . $this->sep() . 'ojs2.tar.gz',
            $this->getDataDir() . $this->sep() . 'sandbox'
        );

        $this->getStub()->callMethod('loadSchema');

        $tableDefinitions = array_map(
            'basename',
            Registry::get('FileSystemManager')->listdir(
                Registry::get('SchemaDir')
            )
        );

        $this->assertTrue(
            in_array('access_keys.json', $tableDefinitions) &&
            in_array('roles.json', $tableDefinitions) &&
            in_array('users.json', $tableDefinitions) &&
            in_array('user_settings.json', $tableDefinitions) &&
            in_array('announcements.json', $tableDefinitions) &&
            in_array('journals.json', $tableDefinitions) &&
            in_array('announcement_settings.json', $tableDefinitions) &&
            in_array('journal_settings.json', $tableDefinitions) &&
            in_array('plugin_settings.json', $tableDefinitions) &&
            in_array('issues.json', $tableDefinitions) &&
            in_array('issue_settings.json', $tableDefinitions)
        );
    }
}
