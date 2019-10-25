<?php 

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\ConfigHandler;

/////////// interfaces //////////////////////
use BeAmado\OjsMigrator\StubInterface;
/////////////////////////////////////////////

//////////// traits /////////////////////////
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\WorkWithFiles;
use BeAmado\OjsMigrator\WorkWithOjsDir;
/////////////////////////////////////////////

use BeAmado\OjsMigrator\Util\FileSystemManager;
use BeAmado\OjsMigrator\Util\ArchiveManager;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\OjsScenarioTester;

class ConfigHandlerTest extends TestCase implements StubInterface
{
    use WorkWithFiles;
    use WorkWithOjsDir;

    public static function setUpBeforeClass() : void
    {
        (new OjsScenarioTester())->prepareStage();
    }

    public static function tearDownAfterClass() : void
    {
        (new OjsScenarioTester())->removeSandbox();
    }

    public function getStub()
    {
        return new class($this->getOjsConfigFile()) extends ConfigHandler {
            use TestStub;
        };
    }

    public function testCanRetrieveFilesDirLocation()
    {
        $location = Registry::get('ConfigHandler')
                    ->getFilesDir();

        $this->assertSame(
            $this->getOjsFilesDir(),
            $location
        );
    }

    public function testCanRetrieveConnectionSettings()
    {
        $connData = (new ConfigHandler($this->getOjsConfigFile()))
                    ->getConnectionSettings();

        $expected = array(
            'host' => 'localhost',
            'username' => 'ojs_user',
            'password' => 'ojs',
        );

        if (array_search('pdo_sqlite', get_loaded_extensions())) {
            $expected['driver'] = 'sqlite';
            $expected['name'] = $this->getOjsDir() 
                . $this->sep() . 'tests_ojs.db';
        } else if (array_search('pdo_mysql', get_loaded_extensions())) {
            $expected['driver'] = 'mysql';
            $expected['name'] = 'tests_ojs';
        } else {
            $this->markTestSkipped('Does not have either sqlite or mysql');
        }

        $this->assertEquals(
            $expected,
            $connData
        );
    }
}
