<?php 

use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Util\ConfigHandler;

/////////// interfaces //////////////////////
use BeAmado\OjsMigrator\Test\StubInterface;
/////////////////////////////////////////////

//////////// traits /////////////////////////
use BeAmado\OjsMigrator\Test\TestStub;
use BeAmado\OjsMigrator\Test\WorkWithFiles;
use BeAmado\OjsMigrator\Test\WorkWithOjsDir;
/////////////////////////////////////////////

use BeAmado\OjsMigrator\Registry;

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

        switch(Registry::get('ConnectionManager')->getDbDriver()) {
            case 'sqlite':
                $expected['name'] = $this->getOjsDir() 
                    . $this->sep() . 'tests_ojs.db';
                $expected['driver'] = 'sqlite';
                break;
            case 'mysql':
                $expected['name'] = 'tests_ojs';
                $expected['driver'] = 'mysql';
        }

        $this->assertEquals(
            $expected,
            $connData
        );
    }

    public function testCanReadTheConfigurationWithQuotes()
    {
        $str = '"batman" = \'bruce wayne\'';
        $data = $this->getStub()->callMethod(
            'getConfigData',
            $str
        );

        $this->assertSame(
            'batman:bruce wayne',
            implode(':', $data)
        );
    }
}
