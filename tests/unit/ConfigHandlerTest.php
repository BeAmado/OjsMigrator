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

        $this->assertEquals(
            array(
                'host' => 'localhost',
                'driver' => 'mysql',
                'username' => 'ojs_user',
                'password' => 'ojs',
                'name' => 'tests_ojs2',
            ),
            $connData
        );
    }
}
