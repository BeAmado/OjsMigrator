<?php

namespace BeAmado\OjsMigrator;

trait WorkWithXmlSchema
{
    public function schemaArray()
    {
        return array(
            'journals' => array(
                'columns' => array(
                    'journal_id' => array(
                        'type' => 'integer',
                        'sql_type' => 'bigint',
                        'nullable' => false,
                        'default' => null,
                        'auto_increment' => true,
                        'primary_key' => true,
                    ),
                    'path' => array(
                        'type' => 'string',
                        'sql_type' => 'varchar(32)',
                        'nullable' => false,
                        'unique' => true,
                    ),
                    'seq' => array(
                        'type' => 'float',
                        'sql_type' => 'double',
                        'nullable' => false,
                        'default' => 0,
                    ),
                    'primary_locale' => array(
                        'type' => 'string',
                        'sql_type' => 'varchar(5)',
                        'nullable' => false,
                        'default' => 'en',
                    ),
                    'enabled' => array(
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
