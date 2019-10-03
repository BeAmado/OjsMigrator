<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\SchemaHandler;
use BeAmado\OjsMigrator\StubInterface;
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\WorkWithFiles;
use BeAmado\OjsMigrator\Util\FileSystemManager;

class SchemaHandlerTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends SchemaHandler {
            use TestStub;
            use WorkWithFiles;

            public function getOjsSchemaFilename()
            {
                return $this->getDataDir()
                    . \BeAmado\OjsMigrator\DIR_SEPARATOR
                    . 'ojs_schema.xml';
            }
        };
    }

    protected function setUp() : void
    {
        $this->sep = \BeAmado\OjsMigrator\DIR_SEPARATOR;
        $this->sandbox = $this->getStub()->getDataDir() 
            . $this->sep 
            . 'sandbox';

        (new FileSystemManager())->createDir($this->sandbox);
    }

    protected function tearDown() : void
    {
        (new FileSystemManager())->removeWholeDir($this->sandbox);
    }

    public function testCanReadSchemaFromTheOjsSchemaFile()
    {
        $schema = (new SchemaHandler())->createFromFile(
            $this->getStub()->getOjsSchemaFilename()
        );

        $this->assertInstanceOf(
            \BeAmado\OjsMigrator\Db\Schema::class,
            $schema
        );
    }
}
