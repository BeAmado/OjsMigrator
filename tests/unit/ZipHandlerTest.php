<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\ZipHandler;
use BeAmado\OjsMigrator\Util\FileHandler;
use BeAmado\OjsMigrator\StubInterface;
use BeAmado\OjsMigrator\Util\FileSystemManager;

class ZipHandlerTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends ZipHandler {
            use BeAmado\OjsMigrator\TestStub;
            use BeAmado\OjsMigrator\WorkWithFiles;
        };
    }

    protected function setUp() : void
    {
        $this->sep = \BeAmado\OjsMigrator\DIR_SEPARATOR;
        $this->sandbox = $this->getStub()->getDataDir() . 
            $this->sep . 'sandbox';
        (new FileSystemManager())->createDir($this->sandbox);

        (new FileSystemManager())->copyFile(
            $this->getStub()->getDataDir() . $this->sep . 'bands.xml',
            $this->sandbox . $this->sep . 'bands.xml'
        );
    }

    protected function tearDown() : void
    {
        (new FileSystemManager())->removeWholeDir($this->sandbox);
    }

    public function testCanGzipAFile()
    {
        $bandsFile = $this->sandbox . $this->sep . 'bands.xml';

        $this->assertTrue(
            (new ZipHandler())->gzip($bandsFile) &&
            (new FileSystemManager())->fileExists($bandsFile . '.gz')
        );
    }

    public function testCanGunzipAFile()
    {
        $bandsFile = $this->sandbox . $this->sep . 'bands.xml';   

        (new ZipHandler())->gzip($bandsFile);

        if ((new FileSystemManager())->fileExists($bandsFile)) {
            (new FileSystemManager())->removeFile($bandsFile);
        }

        $bandsXmlContent = (new FileHandler())->read(
            $this->getStub()->getDataDir() . $this->sep . 'bands.xml'
        );

        $this->assertTrue(
            (new ZipHandler())->gunzip($bandsFile) &&
            (new FileHandler())->read($bandsFile) === $bandsXmlContent
        );
    }
}
