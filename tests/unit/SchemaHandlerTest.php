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
    use WorkWithFiles;
    use WorkWithXmlSchema;

    public function __construct()
    {
        parent::__construct();

        $this->schema = (new SchemaHandler())->createFromFile(
            $this->getOjs2XmlSchemaFilename()
        );
    }

    public function getStub()
    {
        return new class extends SchemaHandler {
            use TestStub;
        };
    }

    protected function setUp() : void
    {
        $this->sandbox = $this->getDataDir() . $this->sep() . 'sandbox';
        (new FileSystemManager())->createDir($this->sandbox);
    }

    protected function tearDown() : void
    {
        (new FileSystemManager())->removeWholeDir($this->sandbox);
    }

    public function testCanReadSchemaFromTheOjsSchemaFile()
    {
        $this->assertEquals(
            $this->journalsSchemaRawArray(),
            $this->schema->get('children')->get(0)->toArray()
        );
    }

    public function testGetTableName()
    {
        $this->assertSame(
            'journals',
            $this->getStub()->callMethod(
                'getTableName',
                $this->schema->get('children')->get(0)
            )
        );
    }
}
