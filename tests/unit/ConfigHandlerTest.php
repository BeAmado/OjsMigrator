<?php 

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\ConfigHandler;

/////////// interfaces //////////////////////
use BeAmado\OjsMigrator\StubInterface;
/////////////////////////////////////////////

//////////// traits /////////////////////////
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\WorkWithFiles;
/////////////////////////////////////////////

use BeAmado\OjsMigrator\Util\FileSystemManager;
use BeAmado\OjsMigrator\Util\ArchiveManager;

class ConfigHandlerTest extends TestCase implements StubInterface
{
    use WorkWithFiles;

    public function getStub()
    {
        return new class($this->getOjs2ConfigFile()) extends ConfigHandler {
            use TestStub;
        };
    }

    public function testCanRetrieveFilesDirLocation()
    {
        $location = (new ConfigHandler($this->getOjs2ConfigFile()))
                    ->getFilesDir();

        $this->assertSame(
            $this->getOjs2FilesDir(),
            $location
        );
    }

    public function testCanRetrieveConnectionSettings()
    {
        $connData = (new ConfigHandler($this->getOjs2ConfigFile()))
                    ->getConnectionSettings();

        $expected = array(
            'host' => 'localhost',
            'username' => 'ojs_user',
            'password' => 'ojs',
        );

        if (array_search('pdo_sqlite', get_loaded_extensions())) {
            $expected['driver'] = 'sqlite';
            $expected['name'] = $this->getOjs2Dir() 
                . $this->sep() . 'tests_ojs.db';
        } else if (array_search('pdo_mysql', get_loaded_extensions())) {
            $expected['driver'] = 'mysql';
            $expected['name'] = 'tests_ojs';
        }

        $this->assertEquals(
            $expected,
            $connData
        );
    }
}
