<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\ArchiveManager;
use BeAmado\OjsMigrator\StubInterface;
use BeAmado\OjsMigrator\Util\FileHandler;
use BeAmado\OjsMigrator\Util\FileSystemManager;

class ArchiveManagerTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends ArchiveManager {
            use BeAmado\OjsMigrator\TestStub;
            use BeAmado\OjsMigrator\WorkWithFiles;
        };
    }

    protected function setUp(): void
    {
        $this->sandbox = $this->getStub()->getDataDir() . '/sandbox';
        (new FileSystemManager())->createDir($this->sandbox);
    }

    protected function tearDown(): void
    {
        (new FileSystemManager())->removeWholeDir($this->sandbox);
    }

    public function testCanTarSomeDirectory()
    {
        $temp1 = $this->sandbox . '/temp1';

        (new FileSystemManager())->createDir($temp1);

        (new FileHandler())->write(
            $temp1 . '/evil-that-men-do.txt',
            'The evil that man do lives on and on...'
        );

        (new FileHandler())->write(
            $temp1 . '/rime-of-the-ancient-mariner.txt',
            'Sailing on and on and on across the sea...'
        );

        $tarFilename = $this->sandbox . '/temp1.tar';

        (new ArchiveManager())->tar('cf', $tarFilename, $temp1);

        $this->assertTrue(
            (new FileSystemManager())->fileExists($tarFilename)
        );
    }
}
