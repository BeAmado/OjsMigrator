<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\SchemaHandler;

/////////// interfaces ///////////////////
use BeAmado\OjsMigrator\StubInterface;
//////////////////////////////////////////

///////////// traits /////////////////////
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\WorkWithFiles;
use BeAmado\OjsMigrator\WorkWithXmlSchema;
//////////////////////////////////////////

use BeAmado\OjsMigrator\Util\FileSystemManager;

class SchemaHandlerTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends SchemaHandler {
            use TestStub;
            use WorkWithFiles;
            use WorkWithXmlSchema;
        };
    }

    protected function setUp() : void
    {
        $this->sandbox = $this->getStub()->getDataDir() 
            . $this->getStub()->sep() . 'sandbox';

        (new FileSystemManager())->createDir($this->sandbox);
    }

    protected function tearDown() : void
    {
        (new FileSystemManager())->removeWholeDir($this->sandbox);
    }

    public function testCanReadSchemaFromTheOjsSchemaFile()
    {
        $schema = (new SchemaHandler())->createFromFile(
            $this->getStub()->getOjs2XmlSchemaFilename()
        );

        $this->assertInstanceOf(
            \BeAmado\OjsMigrator\Db\Schema::class,
            $schema
        );
    }
}
