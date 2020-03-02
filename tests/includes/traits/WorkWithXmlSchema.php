<?php

namespace BeAmado\OjsMigrator\Test;

trait WorkWithXmlSchema
{
    public function getOjs2XmlSchemaFilename()
    {
        return $this->getOjsPublicHtmlDir()
            . $this->sep() . 'dbscripts'
            . $this->sep() . 'xml'
            . $this->sep() . 'ojs_schema.xml';
    }

    protected function primaryKey()
    {
        return array(
            'name' => 'KEY',
            'text' => null,
            'attributes' => array(),
            'children' => array(),
        );
    }
    
    protected function autoincrement()
    {
        return array(
            'name' => 'AUTOINCREMENT',
            'text' => null,
            'attributes' => array(),
            'children' => array(),
        );
    }
    
    protected function notnull()
    {
        return array(
            'name' => 'NOTNULL',
            'text' => null,
            'attributes' => array(),
            'children' => array(),
        );
    }
    
    protected function default($value)
    {
        return array(
            'name' => 'DEFAULT',
            'text' => null,
            'attributes' => array(
                'value' => $value,
            ),
            'children' => array(),
        );
    }
    
    public function journalsSchemaRawArray()
    {
        return array(
            'name' => 'table',
            'text' => null,
            'attributes' => array(
                'name' => 'journals',
            ),
            'children' => array(
                array(
                    'name' => 'field',
                    'text' => null,
                    'attributes' => array(
                        'name' => 'journal_id',
                        'type' => 'I8',
                    ),
                    'children' => array(
                        $this->primaryKey(),
                        $this->autoincrement(),
                    ),
                ),
                array(
                    'name' => 'field',
                    'text' => null,
                    'attributes' => array(
                        'name' => 'path',
                        'type' => 'C2',
                        'size' => '32',
                    ),
                    'children' => array($this->notnull()),
                ),
                array(
                    'name' => 'field',
                    'text' => null,
                    'attributes' => array(
                        'name' => 'seq',
                        'type' => 'F',
                    ),
                    'children' => array(
                        $this->notnull(),
                        $this->default('0'),
                    ),
                ),
                array(
                    'name' => 'field',
                    'text' => null,
                    'attributes' => array(
                        'name' => 'primary_locale',
                        'type' => 'C2',
                        'size' => '5',
                    ),
                    'children' => array($this->notnull()),
                ),
                array(
                    'name' => 'field',
                    'text' => null,
                    'attributes' => array(
                        'name' => 'enabled',
                        'type' => 'I1',
                    ),
                    'children' => array(
                        $this->notnull(),
                        $this->default('1'),
                    ),
                ),
                array(
                    'name' => 'descr',
                    'text' => 'Journals and basic journal settings.',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'index',
                    'text' => null,
                    'attributes' => array('name' => 'journals_path'),
                    'children' => array(
                        array(
                            'name' => 'col',
                            'text' => 'path',
                            'attributes' => array(),
                            'children' => array(),
                        ),
                        array(
                            'name' => 'UNIQUE',
                            'text' => null,
                            'attributes' => array(),
                            'children' => array(),
                        ),
                    ),
                ),
            ),
        );
    }
    
    public function journalSettingsSchemaRawArray()
    {
        return array(
            'name' => 'table',
            'text' => null,
            'attributes' => array(
                'name' => 'journal_settings',
            ),
            'children' => array(
                array(
                    'name' => 'field',
                    'text' => null,
                    'attributes' => array(
                        'name' => 'journal_id',
                        'type' => 'I8',
                    ),
                    'children' => array(
                        $this->notnull(),
                    ),
                ),
                array(
                    'name' => 'field',
                    'text' => null,
                    'attributes' => array(
                        'name' => 'locale',
                        'type' => 'C2',
                        'size' => '5',
                    ),
                    'children' => array(
                        $this->notnull(),
                        $this->default(''),
                    ),
                ),
                array(
                    'name' => 'field',
                    'text' => null,
                    'attributes' => array(
                        'name' => 'setting_name',
                        'type' => 'C2',
                        'size' => '255',
                    ),
                    'children' => array(
                        $this->notnull(),
                    ),
                ),
                array(
                    'name' => 'field',
                    'text' => null,
                    'attributes' => array(
                        'name' => 'setting_value',
                        'type' => 'X',
                    ),
                    'children' => array(),
                ),
                array(
                    'name' => 'field',
                    'text' => null,
                    'attributes' => array(
                        'name' => 'setting_type',
                        'type' => 'C2',
                        'size' => '6',
                    ),
                    'children' => array(
                        $this->notnull(),
                        array(
                            'name' => 'descr',
                            'text' => '(bool|int|float|string|object)',
                            'attributes' => array(),
                            'children' => array(),
                        ),
                    ),
                ),
                array(
                    'name' => 'descr',
                    'text' => 'Journal settings.',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'index',
                    'text' => null,
                    'attributes' => array(
                        'name' => 'journal_settings_journal_id',
                    ),
                    'children' => array(
                        'name' => 'col',
                        'text' => 'journal_id',
                        'attribute' => array(),
                        'children' => array(),
                    ),
                ),
                array(
                    'name' => 'index',
                    'text' => null,
                    'attributes' => array(
                        'name' => 'journal_settings_pkey',
                    ),
                    'children' => array(
                        array(
                            'name' => 'col',
                            'text' => 'journal_id',
                            'attributes' => array(),
                            'children' => array(),
                        ),
                        array(
                            'name' => 'col',
                            'text' => 'locale',
                            'attributes' => array(),
                            'children' => array(),
                        ),
                        array(
                            'name' => 'col',
                            'text' => 'setting_name',
                            'attributes' => array(),
                            'children' => array(),
                        ),
                        array(
                            'name' => 'UNIQUE',
                            'text' => null,
                            'attributes' => array(),
                            'children' => array(),
                        ),
                    ),
                ),
            ),
        );
    }

    public function schemaArray()
    {
        return array(
            'journals' => array(
                'name' => 'journals',
                'columns' => array(
                    'journal_id' => array(
                        'name' => 'journal_id',
                        'type' => 'integer',
                        'sql_type' => 'bigint',
                        'auto_increment' => true,
                        'primary_key' => true,
                        'nullable' => true,
                    ),
                    'path' => array(
                        'name' => 'path',
                        'type' => 'string',
                        'sql_type' => 'varchar(32)',
                        'nullable' => false,
                        'unique' => true,
                    ),
                    'seq' => array(
                        'name' => 'seq',
                        'type' => 'float',
                        'sql_type' => 'double',
                        'nullable' => false,
                        'default' => 0,
                    ),
                    'primary_locale' => array(
                        'name' => 'primary_locale',
                        'type' => 'string',
                        'sql_type' => 'varchar(5)',
                        'nullable' => false,
                    ),
                    'enabled' => array(
                        'name' => 'enabled',
                        'type' => 'integer',
                        'sql_type' => 'tinyint',
                        'nullable' => false,
                        'default' => 1,
                    ),
                ),
                'primary_keys' => array(
                    'journal_id',
                ),
            ),
        );
    }
}
