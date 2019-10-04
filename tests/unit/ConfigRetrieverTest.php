<?php 

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\ConfigRetriever;

/////////// interfaces //////////////////////
use BeAmado\OjsMigrator\StubInterface;
/////////////////////////////////////////////

//////////// traits /////////////////////////
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\WorkWithFiles;
/////////////////////////////////////////////

use BeAmado\OjsMigrator\Util\FileSystemManager;

class ConfigRetrieverTest extends TestCase implements StubInterface
{
    use WorkWithFiles;

    public function getStub()
    {
        return new class($this->getOjs2ConfigFile()) extends ConfigRetriever {
            use TestStub;
        };
    }

    public function testCanRetrieveFilesDirLocation()
    {
        $location = (new ConfigRetriever($this->getOjs2ConfigFile()))
                    ->getFilesDir();

        $this->assertSame(
            $this->getOjs2FilesDir(),
            $location
        );
    }
}
