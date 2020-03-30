<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\Registry;
use \BeAmado\OjsMigrator\Maestro;
use \BeAmado\OjsMigrator\FiletypeHandler; //interface

class SchemaHandler implements FiletypeHandler
{
    /**
     * Receives an object representing a table definition an outputs an array
     * which the \BeAmado\OjsMigrator\Db\Schema class can interpret as defining
     * a database table.
     *
     * @param \BeAmado\OjsMigrator\MyObject $o
     * @return array
     */
    protected function formatTableDefinitionArray($obj)
    {
        
        if (!Registry::get('TableDefinitionHandler')->isTable($obj))
            return;

        return array(
            'name'         => Registry::get('TableDefinitionHandler')
                                      ->getTableName($obj),
            'columns'      => Registry::get('TableDefinitionHandler')
                                      ->getColumns($obj),
            'primary_keys' => Registry::get('TableDefinitionHandler')
                                      ->getPrimaryKeys($obj)
        );
    }

    /**
     * Creates a new \BeAmado\OjsMigrator\db\Schema instance according to the 
     * xml file defining the schema.
     *
     * @param string $filename
     * @return \BeAmado\OjsMigrator\Db\Schema
     */
    public function createFromFile($filename)
    {
        Registry::set(
            'XmlSchema',
            Registry::get('XmlHandler')->createFromFile($filename)
        );

        if (!\is_a(
            Registry::get('XmlSchema'), 
            \BeAmado\OjsMigrator\MyObject::class
        )) {
            Registry::remove('XmlSchema');
            return;
        }

        Registry::remove('Schema');
        Registry::set('Schema', new Schema());

        /** @var $table \BeAmado\OjsMigrator\MyObject */
        Registry::get('XmlSchema')->get('children')
                                  ->forEachValue(
        function($table) {
            if (Registry::get('TableDefinitionHandler')->isTable($table)) {
                Registry::get('Schema')->setDefinition(
                    Registry::get('TableDefinitionHandler')
                            ->getTableName($table),
                    $this->formatTableDefinitionArray($table)
                );
            }
        });

        return Registry::get('Schema')->cloneInstance();
    }

    public function dumpToFile($filename, $content)
    {
        return Registry::get('JsonHandler')->dumpToFile(
            $filename,
            $content
        );
    }

    public function saveSchema($schema)
    {
        if (!Registry::get('FileSystemManager')->dirExists(
            Registry::get('SchemaDir')
        )) {
            Registry::get('FileSystemManager')->createDir(
                Registry::get('SchemaDir')
            );
        }

        if (\is_a($schema, Schema::class)) {
            $schema->forEachValue(function($table) {
                $this->dumpToFile(
                    Registry::get('FileSystemManager')->formPath(array(
                        Registry::get('SchemaDir'),
                        $table->getTableName() . '.json'
                    )),
                    $table
                );
            });
        } else if (\is_array($schema)) {
            $this->saveSchema(new Schema($schema));
        }
    }

    /**
     * 
     * 
     * @param \BeAmado\OjsMigrator\MyObject $xml
     * @return string
     */
    protected function getSchemaFile($xml)
    {
        if (\strtolower($xml->get('name')->getValue()) !== 'schema')
            return;

        if (!Registry::hasKey('OjsDir'))
            Maestro::setOjsDir();

        return Registry::get('FileSystemManager')->formPath(
            \array_merge(
                \explode(
                    \BeAmado\OjsMigrator\DIR_SEPARATOR,
                    Registry::get('OjsDir')
                ),
                \explode(
                    '/', 
                    $xml->get('attributes')->get('file')->getValue()
                )
            )
        );
    }

    /**
     * 
     * 
     * @param \BeAmado\OjsMigrator\MyObject $xml
     * @return \BeAmado\OjsMigrator\Db\Schema
     */
    protected function getSchema($xml)
    {
        return Registry::get('SchemaHandler')->createFromFile(
            $this->getSchemaFile($xml)
        );
    }

    /**
     * Saves the table definitions from the schema that are in the files 
     * indicated in the xml.
     *
     * @param \BeAmado\OjsMigrator\MyObject $xml
     * @return void
     */
    protected function saveDefinitionsFromSchema($xml)
    {
        $xml->get('children')->forEachValue(function($o) {
            if (\strtolower($o->get('name')->getValue()) !== 'schema')
                return;

            Registry::get('SchemaHandler')->saveSchema($this->getSchema($o));
        });
    }

    public function loadAllSchema()
    {
        $vars = Registry::get('MemoryManager')->create();

        // setting the schema locations file to be 
        // [ojs_dir]/dbscripts/xml/install.xml
        $vars->set(
            'schemaLocationsFile',
            Registry::get('FileSystemManager')->formPath(\array_merge(
                \explode(
                    \BeAmado\OjsMigrator\DIR_SEPARATOR, 
                    Registry::get('OjsDir')
                ),
                array(
                    'dbscripts',
                    'xml',
                    'install.xml',
                )
            ))
        );
        
        if (!Registry::get('FileSystemManager')->fileExists(
            $vars->get('schemaLocationsFile')->getValue()
        ))
            throw new \Exception('The xml file listing the schema locations "'
                . $vars->get('schemaLocationsFile')->getValue()
                . '" does not exist');

        // xmlContent will be the data in the file dbscripts/xml/install.xml
        $vars->set(
            'xmlContent',
            Registry::get('XmlHandler')->createFromFile(
                $vars->get('schemaLocationsFile')->getValue()
            )
        );

        $this->saveDefinitionsFromSchema($vars->get('xmlContent'));

        Registry::get('MemoryManager')->destroy($vars);
        unset($vars);
    }

    public function removeSchemaDir()
    {
        Registry::get('FileSystemManager')->removeWholeDir(
            Registry::get('SchemaDir')
        );
    }

    protected function schemaDirExists()
    {
        return Registry::get('FileSystemManager')->dirExists(
            Registry::get('SchemaDir')
        );
    }

    /**
     * Finds the file where the table schema is defined.
     *
     * @param string $name
     * @return string
     */
    protected function getTableDefinitionFile($name)
    {
        if (!Registry::get('FileSystemManager')->dirExists(
            Registry::get('OjsDir')
        ))
            $this->loadAllSchema();

        Registry::set(
            'filename',
             Registry::get('FileSystemManager')->formPath(array(
                 Registry::get('SchemaDir'),
                 $name . '.json'
             ))
        );

        if (Registry::get('FileSystemManager')->fileExists(
            Registry::get('filename')
        ))
            return Registry::get('filename');
    }

    /**
     * Gets the TableDefinition instance of the specified table.
     *
     * @param string $name
     * @return \BeAmado\OjsMigrator\Db\TableDefinition
     */
    public function getTableDefinition($name)
    {
        if (!$this->schemaDirExists())
            $this->loadAllSchema();


        if (!$this->getTableDefinitionFile($name))
            return;
    
        return new TableDefinition(
            Registry::get('JsonHandler')->createFromFile(
                $this->getTableDefinitionFile($name)
            )
        );
    }

    public function getTablesNames()
    {
        if (
            !$this->schemaDirExists() ||
            false
        )
            $this->loadAllSchema();

        return \array_map(
            function($filename) {
                return \substr(\basename($filename), 0, -5); // removes the .json extension
            }, 
            Registry::get('FileSystemManager')->listdir(
                Registry::get('SchemaDir')
            )
        );
    }

    public function destroy()
    {
        Registry::remove('hasChild');
        Registry::remove('isPk');
        Registry::remove('def');
        Registry::remove('indexes');
        Registry::remove('columns');
        Registry::remove('default');
        Registry::remove('column');
        Registry::remove('indexColumns');
        Registry::remove('XmlSchema');
        Registry::remove('filename');
        Registry::remove('Schema');
    }

    public function tableIsDefined($name)
    {
        $tbDef = $this->getTableDefinition($name);
        if (
            \is_a($tbDef, \BeAmado\OjsMigrator\Db\TableDefinition::class) &&
            \strtolower($tbDef->getTableName()) === \strtolower($name)
        ) {
            Registry::get('MemoryManager')->destroy($tbDef);
            unset($tbDef);
            return true;
        }

        Registry::get('MemoryManager')->destroy($tbDef);
        unset($tbDef);
        return false;
    }
}
