<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Application;
use BeAmado\OjsMigrator\Registry;

///////// interfaces /////////////////
use BeAmado\OjsMigrator\StubInterface;

/////////// traits ///////////////////
use BeAmado\OjsMigrator\TestStub;

class ApplicationTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends Application {
            use TestStub;
        };
    }

    public function testPreload()
    {
        Registry::clear();
        $this->getStub()->callMethod('preload');
        $this->assertTrue(
            Registry::hasKey('MemoryManager') &&
            Registry::hasKey('FileSystemManager') &&
            Registry::hasKey('FileHandler') &&
            Registry::hasKey('ArchiveManager') &&
            Registry::hasKey('DbHandler')
        );
    }
}
