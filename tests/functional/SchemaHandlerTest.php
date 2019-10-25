<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Db\SchemaHandler;
use BeAmado\OjsMigrator\Db\Schema;
use BeAmado\OjsMigrator\Db\TableDefinition;

/////////// interfaces ///////////////////
use BeAmado\OjsMigrator\StubInterface;
//////////////////////////////////////////

///////////// traits /////////////////////
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\WorkWithFiles;
use BeAmado\OjsMigrator\WorkWithXmlSchema;
use BeAmado\OjsMigrator\WorkWithOjsDir;
//////////////////////////////////////////

use BeAmado\OjsMigrator\Util\FileSystemManager;
use BeAmado\OjsMigrator\Util\XmlHandler;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Maestro;

class SchemaHandlerTest extends FunctionalTest implements StubInterface
{
    use WorkWithFiles;
    use WorkWithXmlSchema;
    use WorkWithOjsDir;

    public static function tearDownAfterClass() : void
    {
        parent::tearDownAfterClass();
        Registry::get('SchemaHandler')->removeSchemaDir();
    }

    public function setUp() : void
    {
        $this->schema = (new XmlHandler())->createFromFile(
            $this->getOjs2XmlSchemaFilename()
        );

        //var_dump($this->schema->toArray());
    }

    public function getStub()
    {
        return new class extends SchemaHandler {
            use TestStub;
        };
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

    public function testCreateSchemaFromOjsSchemaFile()
    {
        $schema = (new SchemaHandler())->createFromFile(
            $this->getOjs2XmlSchemaFilename()
        );

        $this->assertInstanceOf(
            TableDefinition::class,
            $schema->getDefinition('sections')
        );
    }

    public function testCreateSchemaJsonFile()
    {
        $schema = (new SchemaHandler())->createFromFile(
            $this->getOjs2XmlSchemaFilename()
        );

        $filename = $this->getDataDir() . $this->sep() . 'ojs_schema.json';

        (new SchemaHandler())->dumpToFile(
            $filename,
            $schema
        );

        $this->assertTrue((new FileSystemManager())->fileExists($filename));

        (new FileSystemManager())->removeFile($filename);
    }

    public function testSaveSchema()
    {
        $schema = (new SchemaHandler())->createFromFile(
            $this->getOjs2XmlSchemaFilename()
        );

        (new SchemaHandler())->saveSchema($schema);

        $this->assertTrue(
            (new FileSystemManager())->fileExists(
                \BeAmado\OjsMigrator\BASE_DIR . $this->sep()
              . 'schema' . $this->sep() . 'journals.json'
            )
        );

        (new FileSystemManager())->removeWholeDir(Registry::get('SchemaDir'));
    }

    public function testLoadSchema()
    {
        //extract the ojs2 dir to /tests/_data/sandbox
        /*Registry::get('ArchiveManager')->tar(
            'xzf',
            $this->getDataDir() . $this->sep() . 'ojs2.tar.gz',
            $this->sandbox
        );

        $ojs2PublicHtmlDir = $this->sandbox 
            . $this->sep() . 'ojs2' 
            . $this->sep() . 'public_html';

        Maestro::setOjsDir($ojs2PublicHtmlDir);*/

        Registry::get('SchemaHandler')->loadAllSchema();

        $tableDefinitions = array_map(
            'basename',
            Registry::get('FileSystemManager')->listdir(
                Registry::get('SchemaDir')
            )
        );

        $this->assertTrue(
            in_array('access_keys.json', $tableDefinitions) &&
            in_array('roles.json', $tableDefinitions) &&
            in_array('users.json', $tableDefinitions) &&
            in_array('user_settings.json', $tableDefinitions) &&
            in_array('announcements.json', $tableDefinitions) &&
            in_array('journals.json', $tableDefinitions) &&
            in_array('announcement_settings.json', $tableDefinitions) &&
            in_array('journal_settings.json', $tableDefinitions) &&
            in_array('plugin_settings.json', $tableDefinitions) &&
            in_array('issues.json', $tableDefinitions) &&
            in_array('issue_settings.json', $tableDefinitions)
        );
    }

    public function testGetUsersTableDefinition()
    {
        /*Registry::get('ArchiveManager')->tar(
            'xzf',
            $this->getDataDir() . $this->sep() . 'ojs2.tar.gz',
            $this->sandbox
        );

        $ojs2PublicHtmlDir = $this->sandbox 
            . $this->sep() . 'ojs2' 
            . $this->sep() . 'public_html';

        Maestro::setOjsDir($ojs2PublicHtmlDir);*/

        $def = Registry::get('SchemaHandler')->getTableDefinition('users');

        $this->assertEquals(
            array('user_id'),
            $def->getPrimaryKeys()
        );
    }
}
