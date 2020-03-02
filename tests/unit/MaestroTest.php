<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Maestro;
use BeAmado\OjsMigrator\Registry;

// traits
use BeAmado\OjsMigrator\Test\WorkWithFiles;

class MaestroTest extends TestCase
{
    use WorkWithFiles;

    public function setUp() : void
    {
        Registry::clear();
    }

    public function testSetASpecifiedOjsDir()
    {
        Maestro::setOjsDir($this->getDataDir());
        $this->assertSame(
            $this->getDataDir(),
            Registry::get('OjsDir')
        );
    }

    public function testSetDefaultOjsDir()
    {
        Maestro::setOjsDir();
        $dir = Registry::get('FileSystemManager')->parentDir(
            \BeAmado\OjsMigrator\BASE_DIR
        );

        $this->assertSame(
            $dir,
            Registry::get('OjsDir')
        );
    }

    public function testSetASpecificSchemaDir()
    {
        Maestro::setSchemaDir($this->getDataDir());
        $this->assertSame(
            $this->getDataDir(),
            Registry::get('SchemaDir')
        );
    }

    public function testSetDefaultSchemaDir()
    {
        Maestro::setSchemaDir();
        $dir = Registry::get('FileSystemManager')->formPathFromBaseDir(
            'schema'
        );

        $this->assertSame(
            $dir,
            Registry::get('SchemaDir')
        );
    }

    public function testGetTheDefaultOjsDir()
    {
        $dir = Registry::get('FileSystemManager')->parentDir(
            \BeAmado\OjsMigrator\BASE_DIR
        );

        $this->assertTrue(
            $dir === Registry::get('OjsDir') &&
            $dir === Maestro::get('OjsDir')
        );
    }

    public function testGetTheDefaultSchemaDir()
    {
        $dir = Registry::get('FileSystemManager')->formPathFromBaseDir(
            'schema'
        );

        $this->assertTrue(
            $dir === Registry::get('SchemaDir') &&
            $dir === Maestro::get('SchemaDir')
        );
    }
}
