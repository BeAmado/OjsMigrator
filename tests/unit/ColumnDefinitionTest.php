<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\ColumnDefinition;
use BeAmado\OjsMigrator\WorkWithXmlSchema;

class ColumnDefinitionTest extends TestCase
{
    use WorkWithXmlSchema;

    public function testGetColumnDefinitionOfJournalPath()
    {
        $path = $this->schemaArray()['journals']['columns']['path'];

        $def = new ColumnDefinition($path, 'path');

        $this->assertTrue(
            $def->isPrimaryKey() === false &&
            $def->isNullable() === false &&
            $def->isAutoIncrement() === false &&
            $def->getDefaultValue() === null &&
            $def->getDataType() === 'string' &&
            $def->getColumnName() === 'path' &&
            $def->getSqlType() === 'varchar(32)'
        );
    }   

    public function testCanGetStringRepresentation()
    {
        $expected = '`enabled` TINYINT NOT NULL DEFAULT 1';

        $enabled = $this->schemaArray()['journals']['columns']['enabled'];
        $repr = (new ColumnDefinition($enabled, 'enabled'))->toString();

        $this->assertSame(
            strtolower($expected),
            strtolower($repr)
        );
    }
}
