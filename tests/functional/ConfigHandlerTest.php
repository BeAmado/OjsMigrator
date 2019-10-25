<?php 

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Util\ConfigHandler;

/////////// interfaces //////////////////////
use BeAmado\OjsMigrator\StubInterface;
/////////////////////////////////////////////

//////////// traits /////////////////////////
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\WorkWithFiles;
use BeAmado\OjsMigrator\WorkWithOjsDir;
/////////////////////////////////////////////

use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\OjsScenarioTester;

class ConfigHandlerTest extends FunctionalTest implements StubInterface
{
    use WorkWithFiles;
    use WorkWithOjsDir;

    public function getStub()
    {
        return new class($this->getOjsConfigFile()) extends ConfigHandler {
            use TestStub;
        };
    }

    public function testCanRetrieveFilesDirLocation()
    {
        $location = Registry::get('ConfigHandler')->getFilesDir();

        $this->assertSame(
            $this->getOjsFilesDir(),
            $location
        );
    }

    public function testCanRetrieveConnectionSettings()
    {
        $connData = Registry::get('ConfigHandler')->getConnectionSettings();

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
