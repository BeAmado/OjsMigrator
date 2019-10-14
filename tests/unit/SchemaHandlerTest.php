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
use BeAmado\OjsMigrator\Registry;

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

    public function testJournalsIsTableAndNotColumn()
    {
        $journals = $this->schema->get('children')->get(0);
        $this->assertTrue(
            $this->getStub()->callMethod(
                'isTable',
                $journals
            ) &&
            !$this->getStub()->callMethod(
                'isColumn',
                $journals
            )
        );
    }

    public function testJournalIdIsColumnAndNotTable()
    {
        $journalId = $this->schema
            ->get('children')->get(0) //journals
            ->get('children')->get(0); //journal_id

        $this->assertTrue(
            !$this->getStub()->callMethod(
                'isTable',
                $journalId
            ) &&
            $this->getStub()->callMethod(
                'isColumn',
                $journalId
            )
        );
    }

    public function testCanGetJournalsTableName()
    {
        $this->assertSame(
            'journals',
            $this->getStub()->callMethod(
                'getTableName',
                $this->schema->get('children')->get(0)
            )
        );
    }

    public function testCanSeeThatJournalPathColumnNameIsPath()
    {
        $this->assertSame(
            'path',
            $this->getStub()->callMethod(
                'getColumnName',
                $this->schema
                     ->get('children')->get(0) //journals
                     ->get('children')->get(1) //path
            )
        );
    }

    public function testCanGetJournalsColumnsDataTypes()
    {
        $journals = $this->schema->get('children')->get(0);
        Registry::set('dataTypes', array());
        Registry::set(
            'stub', 
            new class extends SchemaHandler {
                use TestStub;
            }
        );
        $journals->get('children')->forEachValue(function($columnObj) {
            if (!Registry::get('stub')->callMethod('isColumn', $columnObj))
                return;

            $types = Registry::get('dataTypes');
            Registry::remove('dataTypes');
            
            $types[
                Registry::get('stub')->callMethod('getColumnName', $columnObj)
            ] = Registry::get('stub')->callMethod(
                'getDataType',
                $columnObj
            );

            Registry::set('dataTypes', $types);
            unset($types);
        });
        $dataTypes = Registry::get('dataTypes');
        Registry::clear();

        $this->assertEquals(
            array(
                'journal_id' => 'integer',
                'path' => 'string',
                'seq' => 'float',
                'primary_locale' => 'string',
                'enabled' => 'integer',
            ),
            $dataTypes
        );
    }

    public function testCanGetJournalsColumnsSqlDataTypes()
    {
        $journals = $this->schema->get('children')->get(0);
        Registry::set('sqlDataTypes', array());
        Registry::set(
            'stub', 
            new class extends SchemaHandler {
                use TestStub;
            }
        );
        $journals->get('children')->forEachValue(function($columnObj) {
            if (!Registry::get('stub')->callMethod('isColumn', $columnObj))
                return;

            $types = Registry::get('sqlDataTypes');
            Registry::remove('sqlDataTypes');
            
            $types[
                Registry::get('stub')->callMethod('getColumnName', $columnObj)
            ] = Registry::get('stub')->callMethod(
                'getSqlType',
                $columnObj
            );

            Registry::set('sqlDataTypes', $types);
            unset($types);
        });
        $sqlDataTypes = Registry::get('sqlDataTypes');
        Registry::clear();

        $this->assertEquals(
            array(
                'journal_id' => 'bigint',
                'path' => 'varchar(32)',
                'seq' => 'double',
                'primary_locale' => 'varchar(5)',
                'enabled' => 'tinyint',
            ),
            $sqlDataTypes
        );
    }

    public function testJournalIdTypeLastCharIs8()
    {
        $this->assertEquals(
            8,
            $this->getStub()->callMethod(
                'getTypeLastChar',
                $this->schema
                     ->get('children')->get(0) //journals
                     ->get('children')->get(0) // journal_id
            )
        );
    }

    public function testGetIntTypes()
    {
        $this->assertTrue(
            $this->getStub()->callMethod('getIntType', 1) === 'tinyint' &&
            $this->getStub()->callMethod('getIntType', 2) === 'smallint' &&
            $this->getStub()->callMethod('getIntType', 4) === 'int' &&
            $this->getStub()->callMethod('getIntType', 8) === 'bigint'
        );
    }

    public function testCanGetJournalsTableColumns()
    {
        $journals = $this->schema->get('children')->get(0);

        $this->assertSame(
            5,
            count($this->getStub()->callMethod(
                'getColumns',
                $journals
            )->listValues())
        );
    }

    public function testCanGetJournalsTableIndexes()
    {
        $journalSettings= $this->schema->get('children')->get(1);

        $this->assertSame(
            2,
            count($this->getStub()->callMethod(
                'getIndexes',
                $journalSettings
            )->listValues())
        );
    }

    public function testFormatJournalsTableDefinition()
    {
        $journals = $this->schema->get('children')->get(0);
        $arr = $this->getStub()->callMethod(
            'formatTableDefinitionArray',
            $journals
        );

        $this->assertEquals(
            $this->schemaArray()['journals'],
            $arr
        );
    }
}
