<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\Util\XmlHandler;
use \BeAmado\OjsMigrator\Util\JsonHandler;
use \BeAmado\OjsMigrator\Util\MemoryManager;
use \BeAmado\OjsMigrator\Util\FileSystemManager;
use \BeAmado\OjsMigrator\Registry;
use \BeAmado\OjsMigrator\FiletypeHandler; //interface

class SchemaHandler implements FiletypeHandler
{
    public function __construct()
    {
        if (!Registry::hasKey('MemoryManager'))
            Registry::set('MemoryManager', new MemoryManager());
    }

    /**
     * Checks whether or not the given object has the specified name.
     *
     * @param \BeAmado\OjsMigrator\MyObject $o
     * @param string $name
     * @return boolean
     */
    protected function nameIs($o, $name)
    {
        if (
            !\is_a($o, \BeAmado\OjsMigrator\MyObject::class) ||
            !$o->hasAttribute('name')
        ) {
            return false;
        }

        return \strtolower($o->get('name')->getValue()) === \strtolower($name);
    }

    /**
     * Gets the specified attribute which must be present in the attributes 
     * array of the given object.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @param string $attr
     * @return string
     */
    protected function getAttribute($obj, $attr)
    {
        if (
            !\is_a($obj, \BeAmado\OjsMigrator\MyObject::class) ||
            !$obj->hasAttribute('attributes') ||
            !\is_a(
                $obj->get('attributes'), 
                \BeAmado\OjsMigrator\MyObject::class
            )
        ) {
            return;
        }

        if ($obj->get('attributes')->hasAttribute($attr))
            return $obj->get('attributes')->get($attr)->getValue();

        return '';
    }

    /**
     * Gets the text value of an element.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return string
     */
    protected function getTextValue($obj)
    {
        if (
            !\is_a($obj, \BeAmado\OjsMigrator\MyObject::class) ||
            !$obj->hasAttribute('text')
        ) {
            return;
        }

        return $obj->get('text')->getValue();
    }

    /**
     * Checks if the given object has the specified child
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @param string $name
     * @return boolean
     */
    protected function hasChild($obj, $name)
    {
        Registry::remove('hasChild');
        Registry::set('hasChild', false);
        Registry::remove('name');
        Registry::set('name', $name);
        $obj->get('children')->forEachValue(function($child) {
            if ($this->nameIs($child, Registry::get('name')))
                Registry::set('hasChild', true);
        });
        Registry::remove('name');
        return Registry::get('hasChild');
    }





    /////////////// TABLE HANDLING METHODS /////////////////////////////

    /**
     * Check whether or not the given obj is defining a table.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return boolean
     */
    protected function isTable($obj)
    {
        return $this->nameIs($obj, 'table');
    }

    /**
     * Gets the column definitions of the table.
     *
     * @param \BeAmado\OjsMigrator\MyObject $table
     * @return \BeAmado\OjsMigrator\MyObject
     */
    protected function getColumns($table)
    {
        if (!$this->isTable($table))
            return;

        Registry::remove('columns');
        Registry::set(
            'columns',
            Registry::get('MemoryManager')->create()
        );

        $table->get('children')->forEachValue(function($elem) {
            if ($this->isColumn($elem))
                Registry::get('columns')->push($elem);
        });

        return Registry::get('columns')->cloneInstance();
    }

    /**
     * Gets the indexes definitions of the table.
     *
     * @param \BeAmado\OjsMigrator\MyObject $table
     * @return \BeAmado\OjsMigrator\MyObject
     */
    protected function getIndexes($table)
    {
        if (!$this->isTable($table))
            return;

        Registry::remove('indexes');
        Registry::set(
            'indexes',
            Registry::get('MemoryManager')->create()
        );

        $table->get('children')->forEachValue(function($elem) {
            if ($this->isIndex($elem))
                Registry::get('indexes')->push($elem);
        });

        return Registry::get('indexes')->cloneInstance();
    }

    /**
     * Gets the name of the table to be defined
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return string
     */
    protected function getTableName($obj)
    {
        if ($this->isTable($obj)) 
            return $this->getAttribute($obj, 'name');
    }





    ////////////// COLUMN HANDLING METHODS ////////////////////////////
    /**
     * Checks whether or not the given object is defining a table column.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return boolean
     */
    protected function isColumn($obj)
    {
        return $this->nameIs($obj, 'field');
    }

    /**
     * Gets the name of the column being defined for the table
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return string
     */
    protected function getColumnName($obj)
    {
        if ($this->isColumn($obj)) 
            return $this->getAttribute($obj, 'name');
    }

    /**
     * Checks whether or not the given column is defined as a primary key.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return boolean
     */
    protected function isPkColumn($obj)
    {
        if (!$this->isColumn($obj))
            return;

        Registry::remove('isPk');
        Registry::set('isPk', false);

        /** @var $attr \BeAmado\OjsMigrator\MyObject */
        $obj->get('children')->forEachValue(function($attr) {
            if ($this->nameIs($attr, 'key'))
                Registry::set('isPk', true);
        });

        return Registry::get('isPk');
    }

    /**
     * Gets the first letter representing the column type as defined in the
     * xml file representing table schema.
     *
     * @param \BeAmado\OjsMigrator\MyObject
     * @return string
     */
    protected function getTypeFirstChar($obj)
    {
        if (
            $this->isColumn($obj) &&
            $this->getAttribute($obj, 'type')
        ) {
            return \substr($this->getAttribute($obj, 'type'), 0, 1);
        }
    }

    /**
     * Gets the last letter representing the column type as defined in the
     * xml file representing table schema.
     *
     * @param \BeAmado\OjsMigrator\MyObject
     * @return string
     */
    protected function getTypeLastChar($obj)
    {
        if (
            $this->isColumn($obj) &&
            $this->getAttribute($obj, 'type')
        ) {
            return \substr($this->getAttribute($obj, 'type'), -1);
        }
    }

    /**
     * Gets the datatype of the column.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return string
     */
    protected function getDataType($obj)
    {
        if (!$this->isColumn($obj)) 
            return;

        switch(\strtoupper($this->getTypeFirstChar($obj))) { 
        //The first character of the type
            case 'I':
                return 'integer';

            case 'F':
                return 'float';

            case 'X':
            case 'C':
                return 'string';

            default:
                return 'string';
        }
    }

    protected function intTypes()
    {
        return array(
            'tinyint',
            'smallint',
            'int',
            'bigint',
        );
    }

    /**
     * Returns the database integer type which will be the following:
     * 0 - tinyint
     * 1 - smallint
     * 2 - int
     * 3 - bigint
     * 
     * @param integer $number
     * @return string
     */
    protected function getIntType($number)
    {
        if (
            !\is_numeric($number) ||
            $number < 1 ||
            \log($number, 2) >= \count($this->intTypes()) ||
            \fmod(\log($number, 2), 1) != 0 // is not a power of two
        ) {
            return;
        }

        return $this->intTypes()[(int) \log($number, 2)];
    }

    /**
     * Gets the sql type of the column the given obj represents.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return string
     */
    protected function getSqlType($obj)
    {
        if (!$this->isColumn($obj))
            return;

        switch(\strtoupper($this->getTypeFirstChar($obj))) {
            case 'X':
                return 'text';

            case 'I':
                return $this->getIntType($this->getTypeLastChar($obj));

            case 'F':
                return 'double';

            case 'C':
                return $this->getAttribute($obj, 'size') ?
                    'varchar(' . $this->getAttribute($obj, 'size') . ')' :
                    (($this->getTypeLastChar($obj) == 2) ? 'varchar' : 'char');

            case 'D':
                return 'date';

            case 'T':
                return 'datetime';
        }
    }

    /**
     * Gets the default value of the given column.
     *
     * @param \BeAmado\OjsMigrator\MyObject $column
     * @return mixed
     */
    protected function getDefaultValue($column)
    {
        if (
            !$this->isColumn($column) || 
            !$this->hasChild($column, 'default')
        ) {
            return;
        }

        Registry::remove('default');
        Registry::set('default', null);

        $column->get('children')->forEachValue(function($child) {
            if ($this->nameIs($child, 'default'))
                Registry::set(
                    'default', 
                    $this->getAttribute($child, 'VALUE')
                );
        });

        return Registry::get('default');
    }





    ////////////// INDEX HANDLING METHODS /////////////////////////////

    /**
     * Checks whether or not the given object is defining a table index.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return boolean
     */
    protected function isIndex($obj)
    {
        return $this->nameIs($obj, 'index');
    }
    
    /**
     * Gets the name of the index being defined for the table
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return string
     */
    protected function getIndexName($obj)
    {
        if ($this->isIndex($obj)) 
            return $this->getAttribute($obj, 'name');
    }

    /**
     * Checks if the index is defining a column to be unique
     *
     * @param \BeAmado\OjsMigrator\MyObject $index
     * @return boolean
     */
    protected function isUniqueColumnIndex($index)
    {
        if (!$this->isIndex($index))
            return;
        
        Registry::remove('colCount');
        Registry::remove('isUnique');
        Registry::set('colCount', 0);
        Registry::set('isUnique', false);

        $index->get('children')->forEachValue(function($child) {
            if ($this->nameIs($child, 'col'))
                Registry::increment('colCount');

            if ($this->nameIs($child, 'unique'))
                Registry::set('isUnique', true);
        });

        return Registry::get('isUnique') && Registry::get('colCount') === 1;
    }

    /**
     * Checks whether or not the given index object defines primary keys.
     *
     * @param \BeAmado\OjsMigrator\MyObject $index
     * @return boolean
     */
    protected function isPkIndex($index)
    {
        return \strtolower(\substr($this->getIndexName($index), -4)) === 'pkey';
    }

    /**
     * Gets the columns of the index.
     *
     * @param \BeAmado\OjsMigrator\MyObject $index
     * @return array
     */
    protected function getIndexColumns($index)
    {
        if (!$this->isIndex($index))
            return;

        Registry::remove('indexColumns');
        Registry::set(
            'indexColumns', 
            Registry::get('MemoryManager')->create()
        );

        /** @var $o \BeAmado\OjsMigrator\MyObject */
        $index->get('children')->forEachValue(function($o) {
            if ($this->nameIs($o, 'col'))
                Registry::get('indexColumns')->push($this->getTextValue($o));
        });

        return Registry::get('indexColumns')->toArray();
    }


    ///////////////  MOST IMPORTANT METHOD  ///////////////////////////////////

    /**
     * Receives an object representing a table definition an outputs an array
     * which the \BeAmado\OjsMigrator\Db\Schema class can interpret as defining
     * a database table.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return array
     */
    protected function formatTableDefinitionArray($obj)
    {
        if (!$this->isTable($obj))
            return;

        Registry::remove('def');
        Registry::set(
            'def',
            Registry::get('MemoryManager')->create(array(
                'name' => $this->getTableName($obj),
                'columns' => array(),
                'primary_keys' => array(),
            ))
        );

        $this->getColumns($obj)->forEachValue(function($column) {
            Registry::remove('column');
            Registry::set(
                'column', 
                Registry::get('MemoryManager')->create(array(
                    'type' => $this->getDataType($column),
                    'sql_type' => $this->getSqlType($column),
                ))
            );

            if ($this->isPkColumn($column))
                Registry::get('def')->get('primary_keys')
                                    ->push($this->getColumnName($column));

            Registry::get('column')->set(
                'nullable',
                $this->hasChild($column, 'notnull') ? false : true
            );

            if ($this->hasChild($column, 'key'))
                Registry::get('column')->set('primary_key', true);

            if ($this->hasChild($column, 'autoincrement'))
                Registry::get('column')->set('auto_increment', true);

            if ($this->hasChild($column, 'default'))
                Registry::get('column')->set(
                    'default', 
                    $this->getDefaultValue($column)
                );

            Registry::get('def')->get('columns')->set(
                $this->getColumnName($column),
                Registry::get('column')->cloneInstance()
            );

            Registry::remove('column');
        });

        $this->getIndexes($obj)->forEachValue(function($index) {
            if ($this->isPkIndex($index)) {
                $index->get('children')->forEachValue(function ($col) {
                    if ($this->nameIs($col, 'col'))
                        Registry::get('def')->get('primary_keys')
                                            ->push($this->getTextValue($col));
                });
            }

            if ($this->isUniqueColumnIndex($index))
                Registry::get('def')->get('columns')
                                    ->get($this->getIndexColumns($index)[0]) //the name of the column
                                    ->set('unique', true);
        });

        return Registry::get('def')->toArray();
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
        if (!Registry::hasKey('XmlHandler'))
            Registry::set('XmlHandler', new XmlHandler());

        if (!Registry::hasKey('Schema'))
            Registry::set('Schema', new Schema());

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

        /** @var $table \BeAmado\OjsMigrator\MyObject */
        Registry::get('XmlSchema')->get('children')
                                  ->forEachValue(
        function($table) {
            if ($this->isTable($table))
                Registry::get('Schema')->setDefinition(
                    $this->getTableName($table),
                    $this->formatTableDefinitionArray($table)
                );
        });

        return Registry::get('Schema')->cloneInstance();
    }

    public function dumpToFile($filename, $content)
    {
        if (!Registry::hasKey('JsonHandler'))
            Registry::set('JsonHandler', new JsonHandler());

        return Registry::get('JsonHandler')->dumpToFile(
            $filename,
            $content
        );
    }

    public function saveSchema($schema)
    {
        if (!Registry::hasKey('SchemaDir'))
            Registry::set(
                'SchemaDir', 
                \BeAmado\OjsMigrator\BASE_DIR 
              . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'schema'
            );

        if (!Registry::hasKey('FileSystemManager'))
            Registry::set('FileSystemManager', new FileSystemManager());

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
                    Registry::get('SchemaDir') 
                    . \BeAmado\OjsMigrator\DIR_SEPARATOR
                    . $table->getName() . '.json',
                    $table
                );
            });
        } else if (\is_array($schema)) {
            $this->saveSchema(new Schema($schema));
        }
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
    }
}
