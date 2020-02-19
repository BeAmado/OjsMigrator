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
use BeAmado\OjsMigrator\Db\TableDefinitionHandler;
use BeAmado\OjsMigrator\Db\ColumnDefinitionHandler;
use BeAmado\OjsMigrator\Db\IndexDefinitionHandler;

class SchemaHandlerTest extends FunctionalTest implements StubInterface
{
    use WorkWithFiles;
    use WorkWithXmlSchema;
    use WorkWithOjsDir;

    public static function tearDownAfterClass($args = array()) : void
    {
        parent::tearDownAfterClass();
        Registry::get('SchemaHandler')->removeSchemaDir();
    }

    public function setUp() : void
    {
        $this->schema = (new XmlHandler())->createFromFile(
            $this->getOjs2XmlSchemaFilename()
        );
    }

    public function getStub($type = 'SchemaHandler')
    {
        switch (strtolower($type)) {
            case strtolower('SchemaHandler'):
                return new class extends SchemaHandler {
                    use TestStub;
                };

            case strtolower('TableDefinitionHandler'):
                return new class extends TableDefinitionHandler {
                    use TestStub;
                };

            case strtolower('ColumnDefinitionHandler'):
                return new class extends ColumnDefinitionHandler {
                    use TestStub;
                };

            case strtolower('IndexDefinitionHandler'):
                return new class extends IndexDefinitionHandler {
                    use TestStub;
                };
        }
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
            Registry::get('TableDefinitionHandler')->isTable($journals) &&
            !Registry::get('ColumnDefinitionHandler')->isColumn($journals)
        );
    }

    public function testJournalIdIsColumnAndNotTable()
    {
        $journalId = $this->schema
            ->get('children')->get(0) //journals
            ->get('children')->get(0); //journal_id

        $this->assertTrue(
            !Registry::get('TableDefinitionHandler')->isTable($journalId) &&
            Registry::get('ColumnDefinitionHandler')->isColumn($journalId)
        );
    }

    public function testCanGetJournalsTableName()
    {
        $this->assertSame(
            'journals',
            Registry::get('TableDefinitionHandler')->getTableName(
                $this->schema->get('cHilDRen')->get(0)
            )
        );
    }

    public function testCanSeeThatJournalPathColumnNameIsPath()
    {
        $this->assertSame(
            'path',
            Registry::get('ColumnDefinitionHandler')->getColumnName(
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

        $journals->get('children')->forEachValue(function($columnObj) {
            $cdHandler = $this->getStub('ColumnDefinitionHandler');
            if (!$cdHandler->isColumn($columnObj))
                return;

            $types = Registry::get('dataTypes');
            Registry::remove('dataTypes');

            
            $types[$cdHandler->callMethod('getColumnName', $columnObj)] = 
                $cdHandler->callMethod('getDataType', $columnObj);

            Registry::set('dataTypes', $types);
            unset($types);
        });
        $dataTypes = Registry::get('dATaTypeS');

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

        $journals->get('children')->forEachValue(function($columnObj) {
            $cdHandler = $this->getStub('ColumnDefinitionHandler');
            if (!$cdHandler->isColumn($columnObj))
                return;

            $types = Registry::get('sqlDataTypes');
            Registry::remove('sqlDataTypes');
            
            $types[$cdHandler->callMethod('getColumnName', $columnObj)] = 
                $cdHandler->callMethod('getSqlType', $columnObj);

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
            $this->getStub('ColumnDefinitionHandler')->callMethod(
                'getTypeLastChar',
                $this->schema
                     ->get('children')->get(0) //journals
                     ->get('children')->get(0) // journal_id
            )
        );
    }

    public function testGetIntTypes()
    {
        $stub = $this->getStub('ColumnDefinitionHandler');
        $this->assertTrue(
            $stub->callMethod('getIntType', 1) === 'tinyint' &&
            $stub->callMethod('getIntType', 2) === 'smallint' &&
            $stub->callMethod('getIntType', 4) === 'int' &&
            $stub->callMethod('getIntType', 8) === 'bigint'
        );
    }

    public function testCanGetJournalsTableColumns()
    {
        $journals = $this->schema->get('children')->get(0);

        $this->assertSame(
            5,
            count($this->getStub('TableDefinitionHandler')->callMethod(
                'getColumnsRaw',
                $journals
            ))
        );
    }

    public function testCanGetJournalsTableIndexes()
    {
        $journalSettings= $this->schema->get('children')->get(1);

        $this->assertSame(
            2,
            count($this->getStub('TableDefinitionHandler')->callMethod(
                'getIndexesRaw',
                $journalSettings
            ))
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
        $def = Registry::get('SchemaHandler')->getTableDefinition('users');

        $this->assertEquals(
            array('user_id'),
            $def->getPrimaryKeys()
        );
    }

    public function testGetTablesNames()
    {
        $tablesNames = Registry::get('SchemaHandler')->getTablesNames();

        $this->assertTrue(
            in_array('users', $tablesNames) &&
            in_array('user_settings', $tablesNames) &&
            in_array('user_interests', $tablesNames) &&
            in_array('announcements', $tablesNames) &&
            in_array('announcement_settings', $tablesNames) &&
            in_array('journals', $tablesNames) &&
            in_array('journal_settings', $tablesNames) &&
            in_array('plugin_settings', $tablesNames) &&
            in_array('issues', $tablesNames) &&
            in_array('issue_settings', $tablesNames) &&
            in_array('sections', $tablesNames) &&
            in_array('review_forms', $tablesNames)
        );
    }

    public function testCanSeeThatTableIssuesIsDefined()
    {
        $this->assertTrue(
            Registry::get('SchemaHandler')->tableIsDefined('issues')
        );
    }

    public function testCanSeeThatTableAdminIsNotDefined()
    {
        $this->assertFalse(
            Registry::get('SchemaHandler')->tableIsDefined('admin')
        );
    }
}
