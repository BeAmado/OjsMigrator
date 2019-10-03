<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\Schema;
use BeAmado\OjsMigrator\StubInterface;
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\WorkWithXmlSchema;

class SchemaTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends Schema {
            use TestStub;
            use WorkWithXmlSchema;
        };
    }

    public function testGetJournalsTableDefinition()
    {
        $schema = new Schema($this->getStub()->schemaArray());

        $this->assertInstanceOf(
            \BeAmado\OjsMigrator\Db\TableDefinition::class,
            $schema->getDefinition('journals')
        );
    }
}
